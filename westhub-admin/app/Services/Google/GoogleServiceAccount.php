<?php

namespace App\Services\Google;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Mints Google API access tokens from a service-account key.
 *
 * Credentials come from the shared settings table (editable in the admin with
 * no deploy) and fall back to environment config, so an operator can paste a
 * new service account in and it takes effect on the next request.
 */
class GoogleServiceAccount
{
    public function __construct(
        protected string $clientEmail,
        protected string $privateKey,
    ) {
    }

    public function isUsable(): bool
    {
        return $this->clientEmail !== '' && $this->privateKey !== '';
    }

    public function clientEmail(): string
    {
        return $this->clientEmail;
    }

    /**
     * Accepts either a raw PEM private key or a full service-account JSON blob,
     * so an operator can paste whichever Google gave them.
     */
    public static function fromSecret(?string $clientEmail, ?string $secret): self
    {
        $secret = trim((string) $secret);
        $clientEmail = trim((string) $clientEmail);

        if ($secret !== '' && str_starts_with($secret, '{')) {
            $decoded = json_decode($secret, true);

            if (is_array($decoded)) {
                $clientEmail = trim((string) ($decoded['client_email'] ?? $clientEmail));
                $secret = trim((string) ($decoded['private_key'] ?? ''));
            }
        }

        return new self($clientEmail, self::normalizePrivateKey($secret));
    }

    protected static function normalizePrivateKey(string $raw): string
    {
        if ($raw === '') {
            return '';
        }

        // Env files and textareas commonly carry literal "\n" instead of newlines.
        return str_replace(['\n', "\r\n"], ["\n", "\n"], $raw);
    }

    public function accessToken(string $scope): string
    {
        if (! $this->isUsable()) {
            throw new RuntimeException('Google service account credentials are not configured.');
        }

        $cacheKey = 'google_sa_token:'.sha1($this->clientEmail.'|'.$scope.'|'.substr(sha1($this->privateKey), 0, 12));

        return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($scope): string {
            $issuedAt = time();

            $assertion = $this->signJwt([
                'iss' => $this->clientEmail,
                'scope' => $scope,
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $issuedAt,
                'exp' => $issuedAt + 3600,
            ]);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]);

            if ($response->failed()) {
                throw new RuntimeException('Google OAuth token request failed: '.$response->body());
            }

            $token = (string) $response->json('access_token');

            if ($token === '') {
                throw new RuntimeException('Google OAuth token response did not include an access token.');
            }

            return $token;
        });
    }

    /** Drop any cached token so a credential change takes effect immediately. */
    public function forgetToken(string $scope): void
    {
        Cache::forget('google_sa_token:'.sha1($this->clientEmail.'|'.$scope.'|'.substr(sha1($this->privateKey), 0, 12)));
    }

    protected function signJwt(array $claims): string
    {
        $segments = [
            $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR)),
            $this->base64Url(json_encode($claims, JSON_THROW_ON_ERROR)),
        ];

        $signingInput = implode('.', $segments);
        $key = openssl_pkey_get_private($this->privateKey);

        if ($key === false) {
            throw new RuntimeException('The Google service account private key is invalid. Paste the full key including the BEGIN and END lines, or the whole JSON key file.');
        }

        $signature = '';
        $signed = openssl_sign($signingInput, $signature, $key, OPENSSL_ALGO_SHA256);

        if (! $signed) {
            throw new RuntimeException('Unable to sign the Google OAuth assertion.');
        }

        $segments[] = $this->base64Url($signature);

        return implode('.', $segments);
    }

    protected function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
