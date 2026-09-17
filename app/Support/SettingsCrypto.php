<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Encrypts and decrypts values stored in the shared `settings` table.
 *
 * The public site and the admin app are separate Laravel applications with
 * different APP_KEYs, so APP_KEY-based encryption cannot round-trip between
 * them. This helper prefers a shared SETTINGS_ENCRYPTION_KEY and falls back to
 * APP_KEY, and on read it tries every candidate key so values written before
 * the shared key was configured can still be recovered.
 */
class SettingsCrypto
{
    public static function sharedKeyConfigured(): bool
    {
        return self::normalizeKey(config('settings.encryption_key')) !== null;
    }

    public static function encrypt(string $value): string
    {
        $keys = self::candidateKeys();

        if ($keys === []) {
            throw new \RuntimeException('No usable encryption key: set SETTINGS_ENCRYPTION_KEY or APP_KEY.');
        }

        return self::encrypterFor(reset($keys))->encryptString($value);
    }

    public static function decrypt(string $payload): ?string
    {
        foreach (self::candidateKeys() as $key) {
            try {
                return self::encrypterFor($key)->decryptString($payload);
            } catch (DecryptException) {
                continue;
            } catch (Throwable) {
                continue;
            }
        }

        Log::warning('SettingsCrypto could not decrypt a stored setting value. If this value was saved by the other app, set the same SETTINGS_ENCRYPTION_KEY in both.');

        return null;
    }

    /**
     * Preferred key first, then fallbacks used only for reading legacy values.
     *
     * @return array<int, string>
     */
    protected static function candidateKeys(): array
    {
        $keys = [];

        foreach ([config('settings.encryption_key'), config('app.key')] as $raw) {
            $key = self::normalizeKey($raw);

            if ($key !== null && ! in_array($key, $keys, true)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    protected static function normalizeKey(mixed $raw): ?string
    {
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        $raw = trim($raw);

        if (str_starts_with($raw, 'base64:')) {
            $decoded = base64_decode(substr($raw, 7), true);

            return $decoded === false ? null : $decoded;
        }

        return $raw;
    }

    protected static function encrypterFor(string $key): Encrypter
    {
        return new Encrypter($key, (string) config('settings.cipher', 'aes-256-cbc'));
    }
}
