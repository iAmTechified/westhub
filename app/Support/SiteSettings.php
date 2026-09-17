<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Live reader for the shared `settings` table.
 *
 * Values are read straight from the database so a change saved in the admin
 * takes effect on the public site immediately, with no deploy and no
 * config:cache clear. Reads are memoised per request, and the whole group is
 * loaded in one query the first time any key in it is requested.
 */
class SiteSettings
{
    /** @var array<string, array<string, string|null>> group => key => value */
    protected static array $groups = [];

    protected static ?bool $tableExists = null;

    public static function get(string $group, string $key, ?string $default = null): ?string
    {
        $values = self::group($group);

        $value = $values[$key] ?? null;

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

    /**
     * @return array<string, string|null>
     */
    public static function group(string $group): array
    {
        if (array_key_exists($group, self::$groups)) {
            return self::$groups[$group];
        }

        return self::$groups[$group] = self::loadGroup($group);
    }

    /**
     * Drop the per-request memo. Useful in tests and long-running workers.
     */
    public static function flush(?string $group = null): void
    {
        if ($group === null) {
            self::$groups = [];
            self::$tableExists = null;

            return;
        }

        unset(self::$groups[$group]);
    }

    /**
     * @return array<string, string|null>
     */
    protected static function loadGroup(string $group): array
    {
        try {
            if (! self::settingsTableExists()) {
                return [];
            }

            return Setting::query()
                ->where('group', $group)
                ->get(['key', 'value', 'is_encrypted'])
                ->mapWithKeys(function (Setting $setting): array {
                    return [$setting->key => self::decode($setting)];
                })
                ->all();
        } catch (Throwable $e) {
            Log::warning('SiteSettings could not read the settings group "'.$group.'": '.$e->getMessage());

            return [];
        }
    }

    /**
     * Check if the settings table exists, caching the result for the lifetime
     * of this request so we avoid repeated information_schema queries.
     */
    protected static function settingsTableExists(): bool
    {
        if (self::$tableExists !== null) {
            return self::$tableExists;
        }

        $connection = config('database.content_connection', 'content');

        return self::$tableExists = Schema::connection($connection)->hasTable('settings');
    }

    protected static function decode(Setting $setting): ?string
    {
        if (is_null($setting->value)) {
            return null;
        }

        // Note the strict comparison: "0" is a meaningful value (a boolean
        // setting switched off) but it is falsy in PHP, so ?: would discard it.
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

    /**
     * Which appointment provider the site should use: "calendly" or "google".
     */
    public static function appointmentProvider(): string
    {
        $provider = strtolower((string) self::get('appointments', 'provider', config('services.appointments.provider', 'calendly')));

        return in_array($provider, ['calendly', 'google'], true) ? $provider : 'calendly';
    }

    public static function calendlyAppointmentUrl(): ?string
    {
        $url = self::get('appointments', 'calendly_url', config('services.calendly.appointment_url'));

        return filled($url) ? $url : null;
    }

    public static function calendlyAppointmentUrlFor(Appointment $appointment): ?string
    {
        $url = self::calendlyAppointmentUrl();

        if (! $url) {
            return null;
        }

        $params = array_filter([
            'name' => $appointment->full_name,
            'email' => $appointment->email,
            'utm_source' => 'westhub',
            'utm_medium' => 'website',
            'utm_campaign' => 'book_appointment',
            'utm_content' => 'appointment_' . $appointment->id,
        ], fn ($value) => filled($value));

        return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    public static function appointmentTimezone(): string
    {
        return (string) (self::get('appointments', 'timezone') ?: config('app.timezone', 'UTC'));
    }
}
