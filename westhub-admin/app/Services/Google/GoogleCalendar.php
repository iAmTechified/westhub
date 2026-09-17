<?php

namespace App\Services\Google;

use App\Support\SiteSettings;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Settings-driven Google Calendar client.
 *
 * Unlike the previous env-only version this can read free/busy to offer real
 * slots, invites the client as an attendee, and can attach a Google Meet link.
 * Credentials live in the shared settings table so the calendar account can be
 * switched from the admin without a deploy.
 */
class GoogleCalendar
{
    public const SCOPE = 'https://www.googleapis.com/auth/calendar';

    public function isEnabled(): bool
    {
        return SiteSettings::appointmentProvider() === 'google' && $this->isConfigured();
    }

    public function isConfigured(): bool
    {
        return $this->calendarId() !== '' && $this->account()->isUsable();
    }

    public function calendarId(): string
    {
        return trim((string) SiteSettings::get(
            'appointments',
            'google_calendar_id',
            config('services.google_calendar.calendar_id')
        ));
    }

    public function account(): GoogleServiceAccount
    {
        return GoogleServiceAccount::fromSecret(
            SiteSettings::get('appointments', 'google_service_account_email', config('services.google_calendar.service_account_email')),
            SiteSettings::get('appointments', 'google_service_account_private_key', config('services.google_calendar.private_key')),
        );
    }

    public function timezone(): string
    {
        $tz = trim((string) SiteSettings::get('appointments', 'timezone', config('services.google_calendar.timezone')));

        if ($tz !== '' && in_array($tz, timezone_identifiers_list(), true)) {
            return $tz;
        }

        return (string) config('app.timezone', 'UTC');
    }

    public function slotMinutes(): int
    {
        return max(15, SiteSettings::int('appointments', 'google_event_duration_minutes', (int) config('services.google_calendar.default_event_duration', 60)));
    }

    public function meetEnabled(): bool
    {
        return SiteSettings::bool('appointments', 'google_meet_enabled', false);
    }

    /**
     * Business days as ISO day numbers (1 = Monday ... 7 = Sunday).
     *
     * @return array<int, int>
     */
    public function businessDays(): array
    {
        $raw = (string) SiteSettings::get('appointments', 'google_business_days', '1,2,3,4,5');

        $days = array_values(array_filter(array_map(
            static fn (string $part): int => (int) trim($part),
            explode(',', $raw)
        ), static fn (int $day): bool => $day >= 1 && $day <= 7));

        return $days !== [] ? $days : [1, 2, 3, 4, 5];
    }

    public function businessStart(): string
    {
        return $this->timeSetting('google_business_hours_start', '09:00');
    }

    public function businessEnd(): string
    {
        return $this->timeSetting('google_business_hours_end', '17:00');
    }

    public function minNoticeHours(): int
    {
        return max(0, SiteSettings::int('appointments', 'google_min_notice_hours', 24));
    }

    public function bookingWindowDays(): int
    {
        return max(1, SiteSettings::int('appointments', 'google_booking_window_days', 30));
    }

    protected function timeSetting(string $key, string $default): string
    {
        $value = trim((string) SiteSettings::get('appointments', $key, $default));

        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) === 1 ? $value : $default;
    }

    /**
     * Candidate start times for a day, with anything Google reports as busy
     * removed. Times are returned in the configured booking timezone.
     *
     * @return array<int, array{start: string, end: string, label: string}>
     */
    public function availableSlots(Carbon $day): array
    {
        $tz = $this->timezone();
        $day = $day->copy()->setTimezone($tz)->startOfDay();

        if (! in_array((int) $day->isoWeekday(), $this->businessDays(), true)) {
            return [];
        }

        $duration = $this->slotMinutes();
        [$startHour, $startMinute] = array_map('intval', explode(':', $this->businessStart()));
        [$endHour, $endMinute] = array_map('intval', explode(':', $this->businessEnd()));

        $cursor = $day->copy()->setTime($startHour, $startMinute);
        $closing = $day->copy()->setTime($endHour, $endMinute);
        $earliest = Carbon::now($tz)->addHours($this->minNoticeHours());

        $candidates = [];

        while ($cursor->copy()->addMinutes($duration)->lessThanOrEqualTo($closing)) {
            $slotStart = $cursor->copy();
            $slotEnd = $cursor->copy()->addMinutes($duration);

            if ($slotStart->greaterThanOrEqualTo($earliest)) {
                $candidates[] = [$slotStart, $slotEnd];
            }

            $cursor->addMinutes($duration);
        }

        if ($candidates === []) {
            return [];
        }

        $busy = $this->busyPeriods(
            $day->copy()->setTime($startHour, $startMinute),
            $day->copy()->setTime($endHour, $endMinute)
        );

        $slots = [];

        foreach ($candidates as [$slotStart, $slotEnd]) {
            $overlaps = false;

            foreach ($busy as [$busyStart, $busyEnd]) {
                if ($slotStart->lessThan($busyEnd) && $slotEnd->greaterThan($busyStart)) {
                    $overlaps = true;
                    break;
                }
            }

            if (! $overlaps) {
                $slots[] = [
                    'start' => $slotStart->toIso8601String(),
                    'end' => $slotEnd->toIso8601String(),
                    'label' => $slotStart->format('g:i A'),
                ];
            }
        }

        return $slots;
    }

    /**
     * @return array<int, array{0: Carbon, 1: Carbon}>
     */
    public function busyPeriods(Carbon $from, Carbon $to): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        try {
            $response = $this->request()->post('https://www.googleapis.com/calendar/v3/freeBusy', [
                'timeMin' => $from->copy()->toIso8601String(),
                'timeMax' => $to->copy()->toIso8601String(),
                'timeZone' => $this->timezone(),
                'items' => [['id' => $this->calendarId()]],
            ]);

            if ($response->failed()) {
                throw new RuntimeException('Google Calendar free/busy failed: ' . $response->body());
            }

            $periods = (array) $response->json('calendars.' . $this->calendarId() . '.busy', []);
            $tz = $this->timezone();

            return array_values(array_filter(array_map(static function ($period) use ($tz): ?array {
                $start = data_get($period, 'start');
                $end = data_get($period, 'end');

                if (! $start || ! $end) {
                    return null;
                }

                return [Carbon::parse($start)->setTimezone($tz), Carbon::parse($end)->setTimezone($tz)];
            }, $periods)));
        } catch (Throwable $e) {
            // Never let an availability lookup take the booking form down. An
            // empty busy list means we offer every business slot and rely on the
            // database conflict check instead.
            report($e);

            return [];
        }
    }

    /**
     * Create or update the calendar event for a booking.
     *
     * @param  array{summary: string, description?: string, location?: string, start: Carbon, end: Carbon, attendee_email?: string, attendee_name?: string}  $details
     * @return array{id: string, html_link: ?string, meet_url: ?string}
     */
    public function upsertEvent(array $details, ?string $existingEventId = null): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Google Calendar is not configured.');
        }

        $payload = [
            'summary' => $details['summary'],
            'description' => $details['description'] ?? null,
            'location' => $details['location'] ?? null,
            'start' => ['dateTime' => $details['start']->toIso8601String(), 'timeZone' => $this->timezone()],
            'end' => ['dateTime' => $details['end']->toIso8601String(), 'timeZone' => $this->timezone()],
        ];

        if (filled($details['attendee_email'] ?? null)) {
            $payload['attendees'] = [[
                'email' => $details['attendee_email'],
                'displayName' => $details['attendee_name'] ?? null,
                'responseStatus' => 'needsAction',
            ]];
        }

        $query = ['sendUpdates' => 'all'];

        if ($this->meetEnabled() && ! $existingEventId) {
            $payload['conferenceData'] = [
                'createRequest' => [
                    'requestId' => (string) \Illuminate\Support\Str::uuid(),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ];
            $query['conferenceDataVersion'] = 1;
        }

        $base = 'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($this->calendarId()) . '/events';
        $url = $existingEventId
            ? $base . '/' . rawurlencode($existingEventId) . '?' . http_build_query($query)
            : $base . '?' . http_build_query($query);

        $response = $existingEventId
            ? $this->request()->put($url, array_filter($payload, static fn ($v) => ! is_null($v)))
            : $this->request()->post($url, array_filter($payload, static fn ($v) => ! is_null($v)));

        if ($response->failed()) {
            throw new RuntimeException('Google Calendar event write failed: ' . $response->body());
        }

        return [
            'id' => (string) $response->json('id'),
            'html_link' => $response->json('htmlLink'),
            'meet_url' => $response->json('hangoutLink'),
        ];
    }

    public function deleteEvent(string $eventId): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $response = $this->request()->delete(
            'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($this->calendarId()) . '/events/' . rawurlencode($eventId) . '?sendUpdates=all'
        );

        // A 404/410 means it is already gone, which is the outcome we wanted.
        if ($response->failed() && ! in_array($response->status(), [404, 410], true)) {
            throw new RuntimeException('Google Calendar event delete failed: ' . $response->body());
        }
    }

    /**
     * Backs the admin "Test connection" button.
     *
     * @return array{ok: bool, message: string}
     */
    public function testConnection(): array
    {
        if ($this->calendarId() === '') {
            return ['ok' => false, 'message' => 'No calendar ID has been set.'];
        }

        if (! $this->account()->isUsable()) {
            return ['ok' => false, 'message' => 'Service account email or private key is missing.'];
        }

        try {
            $this->account()->forgetToken(self::SCOPE);

            $response = $this->request()->get(
                'https://www.googleapis.com/calendar/v3/calendars/' . rawurlencode($this->calendarId())
            );

            if (in_array($response->status(), [403, 404], true)) {
                return ['ok' => false, 'message' => 'Access denied or calendar not found. In Google Calendar, share this calendar with ' . $this->account()->clientEmail() . ' and grant "Make changes to events".'];
            }

            if ($response->failed()) {
                return ['ok' => false, 'message' => 'Google rejected the request: ' . $response->body()];
            }

            $summary = (string) $response->json('summary', $this->calendarId());
            $slots = count($this->availableSlots(Carbon::now($this->timezone())->addDay()));

            return ['ok' => true, 'message' => 'Connected to "' . $summary . '". Tomorrow has ' . $slots . ' open slot(s) under the current hours.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    protected function request(): PendingRequest
    {
        return Http::withToken($this->account()->accessToken(self::SCOPE))
            ->acceptJson()
            ->timeout(20);
    }
}
