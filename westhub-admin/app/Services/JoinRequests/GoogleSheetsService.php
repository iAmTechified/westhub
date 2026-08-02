<?php

namespace App\Services\JoinRequests;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleSheetsService
{
    public const HEADERS = [
        'westhub_id',
        'submitted_at',
        'full_name',
        'email',
        'phone',
        'applicant_type',
        'position/profession',
        'date_available',
        'sheet_stage',
        'westhub_decision',
        'owner',
        'notes',
        'admin_url',
        'resume_url',
        'signature_url',
        'document_urls',
    ];

    private const TOKEN_CACHE_KEY = 'google_join_requests_sheet_access_token';

    public function isConfigured(): bool
    {
        return filled($this->spreadsheetId())
            && filled($this->sheetName())
            && filled($this->serviceAccountEmail())
            && filled($this->privateKey());
    }

    public function updateWesthubDecision(int $westhubId, string $decision): bool
    {
        $this->ensureHeaders();

        $rowNumber = $this->findRowNumber($westhubId);
        if ($rowNumber === null) {
            return false;
        }

        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->put($this->valuesUrl($this->sheetName().'!J'.$rowNumber).'?valueInputOption=USER_ENTERED', [
                'values' => [[$decision]],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Google Sheets decision update failed: '.$response->body());
        }

        return true;
    }

    protected function findRowNumber(int $westhubId): ?int
    {
        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->get($this->valuesUrl($this->sheetName().'!A:A'));

        if ($response->failed()) {
            throw new RuntimeException('Google Sheets row lookup failed: '.$response->body());
        }

        foreach ($response->json('values', []) as $offset => $row) {
            if ((string) ($row[0] ?? '') === (string) $westhubId) {
                return $offset + 1;
            }
        }

        return null;
    }

    protected function ensureHeaders(): void
    {
        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->get($this->valuesUrl($this->sheetName().'!A1:P1'));

        if ($response->failed()) {
            throw new RuntimeException('Google Sheets header check failed: '.$response->body());
        }

        $headers = $response->json('values.0', []);
        if ($headers === []) {
            $writeResponse = Http::withToken($this->accessToken())
                ->acceptJson()
                ->put($this->valuesUrl($this->sheetName().'!A1:P1').'?valueInputOption=RAW', [
                    'values' => [self::HEADERS],
                ]);

            if ($writeResponse->failed()) {
                throw new RuntimeException('Google Sheets header write failed: '.$writeResponse->body());
            }

            return;
        }

        if ($headers !== self::HEADERS) {
            throw new RuntimeException('Google Sheets headers do not match the expected join-request contract.');
        }
    }

    protected function accessToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, now()->addMinutes(50), function (): string {
            $issuedAt = time();
            $assertion = $this->buildSignedJwt([
                'iss' => $this->serviceAccountEmail(),
                'scope' => 'https://www.googleapis.com/auth/spreadsheets',
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
                throw new RuntimeException('Google OAuth token response missing access token.');
            }

            return $token;
        });
    }

    protected function buildSignedJwt(array $claims): string
    {
        $segments = [
            $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR)),
            $this->base64UrlEncode(json_encode($claims, JSON_THROW_ON_ERROR)),
        ];

        $signingInput = implode('.', $segments);
        $privateKey = openssl_pkey_get_private($this->privateKey());
        if ($privateKey === false) {
            throw new RuntimeException('Google Sheets service account private key is invalid.');
        }

        $signature = '';
        $signed = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);

        if (! $signed) {
            throw new RuntimeException('Unable to sign Google Sheets OAuth JWT assertion.');
        }

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    protected function valuesUrl(string $range): string
    {
        return sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s',
            rawurlencode($this->spreadsheetId()),
            rawurlencode($range),
        );
    }

    protected function spreadsheetId(): string
    {
        return (string) config('services.google_sheets.spreadsheet_id', '');
    }

    protected function sheetName(): string
    {
        return (string) config('services.google_sheets.sheet_name', '');
    }

    protected function serviceAccountEmail(): string
    {
        return (string) config('services.google_sheets.service_account_email', '');
    }

    protected function privateKey(): string
    {
        $raw = (string) config('services.google_sheets.private_key', '');

        return $raw === '' ? '' : str_replace('\n', "\n", $raw);
    }
}
