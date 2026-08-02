<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SupportContact
{
    /** @var array<string, bool> Per-request cache for Schema::hasTable checks. */
    private static array $tableExists = [];
    public static function email(): ?string
    {
        $email = self::configuredEmail()
            ?? config('westhub.support_email')
            ?? config('mail.from.address');

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    protected static function configuredEmail(): ?string
    {
        try {
            $connection = config('database.content_connection', 'content');

            if (! self::tableExists($connection, 'settings')) {
                return null;
            }

            foreach ([
                ['mail', 'enquiries_from_address'],
                ['mail', 'from_address'],
                ['email', 'support_address'],
                ['email', 'support_email'],
                ['team', 'review_notifications_email'],
            ] as [$group, $key]) {
                $value = Setting::query()
                    ->where('group', $group)
                    ->where('key', $key)
                    ->value('value');

                if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return $value;
                }
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    /**
     * Check if a table exists, caching the result per-request to avoid
     * repeated information_schema queries on each view render.
     */
    private static function tableExists(string $connection, string $table): bool
    {
        $key = "table_exists_{$connection}_{$table}";

        if (! array_key_exists($key, self::$tableExists)) {
            self::$tableExists[$key] = Schema::connection($connection)->hasTable($table);
        }

        return self::$tableExists[$key];
    }
}
