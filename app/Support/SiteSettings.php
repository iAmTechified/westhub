<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Setting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SiteSettings
{
    protected static array $values = [];

    /** @var array<string, bool> Per-request cache for Schema::hasTable checks. */
    protected static array $tableExists = [];

    public static function get(string $group, string $key, ?string $default = null): ?string
    {
        $cacheKey = "{$group}.{$key}";

        if (array_key_exists($cacheKey, self::$values)) {
            return self::$values[$cacheKey] ?? $default;
        }

        try {
            $connection = config('database.content_connection', 'content');

            if (! self::settingsTableExists($connection)) {
                return self::$values[$cacheKey] = $default;
            }

            $setting = Setting::query()
                ->where('group', $group)
                ->where('key', $key)
                ->first(['value', 'is_encrypted']);

            if (! $setting || is_null($setting->value)) {
                return self::$values[$cacheKey] = $default;
            }

            if (! $setting->is_encrypted) {
                return self::$values[$cacheKey] = trim((string) $setting->value) ?: $default;
            }

            try {
                return self::$values[$cacheKey] = trim(Crypt::decryptString((string) $setting->value)) ?: $default;
            } catch (DecryptException) {
                return self::$values[$cacheKey] = $default;
            }
        } catch (Throwable) {
            return self::$values[$cacheKey] = $default;
        }
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
        return (string) config('app.timezone', 'UTC');
    }

    /**
     * Check if the settings table exists, caching the result for the lifetime
     * of this request so we avoid repeated information_schema queries.
     */
    private static function settingsTableExists(string $connection): bool
    {
        $cacheKey = "table_exists_{$connection}_settings";

        if (! array_key_exists($cacheKey, self::$tableExists)) {
            self::$tableExists[$cacheKey] = Schema::connection($connection)->hasTable('settings');
        }

        return self::$tableExists[$cacheKey];
    }
}
