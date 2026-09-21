<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Live reader for the shared `settings` table, admin side.
 *
 * Values are read straight from the database so a change saved in the admin
 * takes effect immediately, with no deploy and no config:cache clear. Reads are
 * memoised per request and a whole group is loaded in one query.
 *
 * Note on the connection: the admin app has no `content` database connection.
 * This class previously asked for one, the call threw, and the error was
 * swallowed, so every admin read silently returned its default. It now uses
 * the Setting model's own connection.
 */
class SiteSettings
{
    /** @var array<string, array<string, string|null>> group => key => value */
    protected static array $groups = [];

    protected static ?bool $tableExists = null;

    public static function get(string $group, string $key, ?string $default = null): ?string
    {
        $value = self::group($group)[$key] ?? null;

        return is_null($value) || $value === '' ? $default : $value;
    }

    public static function bool(string $group, string $key, bool $default = false): bool
    {
        $value = self::get($group, $key);

        if (is_null($value)) {
            return $default;
        }

        return in_array(strtolower(trim($value)), ['1', 'true', 'on', 'yes', 'enabled'], true);
    }

    public static function int(string $group, string $key, int $default = 0): int
    {
        $value = self::get($group, $key);

        return is_null($value) || ! is_numeric(trim($value)) ? $default : (int) trim($value);
    }

    public static function date(string $group, string $key): ?Carbon
    {
        $value = self::get($group, $key);

        if (is_null($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array<string, string|null> */
    public static function group(string $group): array
    {
        if (array_key_exists($group, self::$groups)) {
            return self::$groups[$group];
        }

        return self::$groups[$group] = self::loadGroup($group);
    }

    public static function flush(?string $group = null): void
    {
        if ($group === null) {
            self::$groups = [];
            self::$tableExists = null;

            return;
        }

        unset(self::$groups[$group]);
    }

    /** @return array<string, string|null> */
    protected static function loadGroup(string $group): array
    {
        try {
            if (! self::settingsTableExists()) {
                return [];
            }

            return Setting::query()
                ->where('group', $group)
                ->get(['key', 'value', 'is_encrypted'])
                ->mapWithKeys(fn (Setting $setting): array => [$setting->key => self::decode($setting)])
                ->all();
        } catch (Throwable $e) {
            Log::warning('SiteSettings could not read the settings group "' . $group . '": ' . $e->getMessage());

            return [];
        }
    }

    protected static function settingsTableExists(): bool
    {
        if (self::$tableExists !== null) {
            return self::$tableExists;
        }

        return self::$tableExists = Schema::connection((new Setting)->getConnectionName())->hasTable('settings');
    }

    protected static function decode(Setting $setting): ?string
    {
        if (is_null($setting->value)) {
            return null;
        }

        // Strict comparison: "0" is a real value (a switched-off toggle) but it
        // is falsy in PHP, so ?: would silently discard it.
        if (! $setting->is_encrypted) {
            $value = trim((string) $setting->value);

            return $value === '' ? null : $value;
        }

        $decrypted = SettingsCrypto::decrypt((string) $setting->value);

        if (is_null($decrypted)) {
            return null;
        }

        $decrypted = trim($decrypted);

        return $decrypted === '' ? null : $decrypted;
    }

    public static function contactEmail(): string
    {
        $email = self::get('mail', 'enquiries_from_address')
            ?? self::get('contact', 'public_email')
            ?? self::get('mail', 'from_address')
            ?? config('westhub.contact.email')
            ?? config('mail.from.address');

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }

    public static function contactPhone(): string
    {
        $phone = self::get('contact', 'public_phone')
            ?? self::get('contact', 'phone')
            ?? config('westhub.contact.phone', '+1 2246250423');

        return trim((string) $phone) ?: '+1 2246250423';
    }

    public static function contactPhoneTel(): string
    {
        $phone = self::contactPhone();
        $digits = preg_replace('/[^\d+]/', '', $phone);

        return str_starts_with($digits, '+') ? $digits : '+' . $digits;
    }

    public static function appointmentsEmail(): string
    {
        $email = self::get('mail', 'appointments_from_address')
            ?? self::get('mail', 'from_address')
            ?? config('mail.from.address');

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }

    public static function applicationsEmail(): string
    {
        $email = self::get('mail', 'applications_from_address')
            ?? self::get('mail', 'from_address')
            ?? config('mail.from.address');

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }

    public static function enquiriesEmail(): string
    {
        return self::contactEmail();
    }

    public static function newsletterEmail(): string
    {
        $email = self::get('mail', 'newsletter_from_address')
            ?? self::get('mail', 'from_address')
            ?? config('mail.from.address');

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }

    public static function fromName(): string
    {
        return self::get('mail', 'from_name') ?? config('mail.from.name', 'WestHub Healthcare');
    }

    public static function contactMailto(?string $subject = 'WestHub Healthcare Enquiry'): string
    {
        $email = self::contactEmail();

        if ($email === '') {
            return '#';
        }

        return 'mailto:' . $email . ($subject ? '?subject=' . rawurlencode($subject) : '');
    }

    /** Which appointment provider to use: "calendly", "google" or "google_booking_page". */
    public static function appointmentProvider(): string
    {
        $provider = strtolower((string) self::get('appointments', 'provider', config('services.appointments.provider', 'calendly')));

        return in_array($provider, ['calendly', 'google', 'google_booking_page'], true) ? $provider : 'calendly';
    }

    public static function googleBookingPageUrl(): ?string
    {
        $url = self::get('appointments', 'google_booking_page_url', config('services.google_booking_page.url'));

        return filled($url) ? trim($url) : null;
    }

    public static function calendlyAppointmentUrl(): ?string
    {
        $url = self::get('appointments', 'calendly_url', config('services.calendly.appointment_url'));

        return filled($url) ? $url : null;
    }

    public static function appointmentTimezone(): string
    {
        return (string) (self::get('appointments', 'timezone') ?: config('app.timezone', 'UTC'));
    }
}
