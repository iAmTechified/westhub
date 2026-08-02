<?php

namespace App\Services\Appointments;

use App\Models\Appointment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleCalendarService
{
    private const TOKEN_CACHE_KEY = 'google_calendar_service_account_access_token';

    public function upsertEvent(Appointment $appointment): ?string
    {
        if (! $this->isConfigured() || ! $appointment->scheduled_at) {
            return null;
        }

        $eventId = (string) data_get($appointment->meta, 'google_event_id', '');
        $payload = $this->buildEventPayload($appointment);
        $calendarId = $this->calendarId();
        $baseUrl = 'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($calendarId) . '/events';

        if ($eventId !== '') {
            $response = Http::withToken($this->accessToken())
                ->acceptJson()
                ->put($baseUrl . '/' . rawurlencode($eventId), $payload);
        } else {
            $response = Http::withToken($this->accessToken())
                ->acceptJson()
                ->post($baseUrl, $payload);
        }

        if ($response->failed()) {
            throw new RuntimeException('Google Calendar sync failed: ' . $response->body());
        }

        return $response->json('id');
    }

    public function deleteEvent(Appointment $appointment): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $eventId = (string) data_get($appointment->meta, 'google_event_id', '');
        if ($eventId === '') {
            return;
        }

        $calendarId = $this->calendarId();
        $url = 'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($calendarId) . '/events/' . rawurlencode($eventId);
        $response = Http::withToken($this->accessToken())->delete($url);

        if ($response->status() === 404) {
            return;
        }

        if ($response->failed()) {
            throw new RuntimeException('Google Calendar delete failed: ' . $response->body());
        }
    }

    public function isConfigured(): bool
    {
        return filled($this->calendarId())
            && filled($this->serviceAccountEmail())
            && filled($this->privateKey());
    }

    protected function buildEventPayload(Appointment $appointment): array
    {
        $start = $appointment->scheduled_at->copy();
        $durationMinutes = (int) config('services.google_calendar.default_event_duration', 60);
        $end = $start->copy()->addMinutes(max($durationMinutes, 15));
        $timezone = (string) config('services.google_calendar.timezone', config('app.timezone'));
        $serviceName = $appointment->service?->name ?? 'General Consultation';
        $location = collect([
            $appointment->township?->name,
            $appointment->county?->name,
        ])->filter()->join(', ');

        return [
            'summary' => 'Appointment: ' . $appointment->full_name . ' - ' . $serviceName,
            'description' => implode("\n", array_filter([
                'Name: ' . $appointment->full_name,
                'Email: ' . $appointment->email,
                $appointment->phone ? 'Phone: ' . $appointment->phone : null,
                'Service: ' . $serviceName,
                $appointment->message ? 'Message: ' . $appointment->message : null,
                'Source: ' . ($appointment->source ?: 'website'),
            ])),
            'location' => $location !== '' ? $location : null,
            'start' => [
                'dateTime' => $start->toIso8601String(),
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $end->toIso8601String(),
                'timeZone' => $timezone,
            ],
        ];
    }

    protected function accessToken(): string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, now()->addMinutes(50), function (): string {
            $issuedAt = time();
            $expiresAt = $issuedAt + 3600;
            $assertion = $this->buildSignedJwt([
                'iss' => $this->serviceAccountEmail(),
                'scope' => 'https://www.googleapis.com/auth/calendar',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $issuedAt,
                'exp' => $expiresAt,
            ]);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]);

            if ($response->failed()) {
                throw new RuntimeException('Google OAuth token request failed: ' . $response->body());
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
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR)),
            $this->base64UrlEncode(json_encode($claims, JSON_THROW_ON_ERROR)),
        ];
        $signingInput = implode('.', $segments);

        $privateKey = openssl_pkey_get_private($this->privateKey());
        if ($privateKey === false) {
            throw new RuntimeException('Google service account private key is invalid.');
        }

        $signature = '';
        $signed = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);

        if (! $signed) {
            throw new RuntimeException('Unable to sign Google OAuth JWT assertion.');
        }

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    protected function calendarId(): string
    {
        return (string) config('services.google_calendar.calendar_id', '');
    }

    protected function serviceAccountEmail(): string
    {
        return (string) config('services.google_calendar.service_account_email', '');
    }

    protected function privateKey(): string
    {
        $raw = (string) config('services.google_calendar.private_key', '');
        if ($raw === '') {
            return '';
        }

        return str_replace('\n', "\n", $raw);
    }
}

