<?php

namespace App\Services\Appointments;

use App\Exceptions\Appointments\SlotUnavailableException;
use App\Models\Appointment;
use App\Services\Google\GoogleCalendar;
use App\Support\SiteSettings;
use Illuminate\Support\Carbon;
use RuntimeException;
use Throwable;

/**
 * One place that answers "how does WestHub schedule appointments right now?".
 *
 * The provider is a setting, so operations can switch between Calendly and
 * Google Calendar from the admin without a deploy. Calendly stays a supported
 * option rather than being replaced.
 */
class AppointmentProviderManager
{
    public const CALENDLY = 'calendly';
    public const GOOGLE = 'google';

    public function __construct(
        protected GoogleCalendar $google,
    ) {
    }

    public function current(): string
    {
        return SiteSettings::appointmentProvider();
    }

    public function isGoogle(): bool
    {
        return $this->current() === self::GOOGLE;
    }

    public function isCalendly(): bool
    {
        return $this->current() === self::CALENDLY;
    }

    public function label(): string
    {
        return $this->isGoogle() ? 'Google Calendar' : 'Calendly';
    }

    public function google(): GoogleCalendar
    {
        return $this->google;
    }

    /**
     * Whether the currently selected provider has everything it needs. When it
     * does not, the booking form falls back to capturing the lead and telling
     * the visitor someone will call.
     */
    public function isConfigured(): bool
    {
        return $this->isGoogle()
            ? $this->google->isConfigured()
            : filled(SiteSettings::calendlyAppointmentUrl());
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function testConnection(): array
    {
        if ($this->isGoogle()) {
            return $this->google->testConnection();
        }

        $url = SiteSettings::calendlyAppointmentUrl();

        if (! $url) {
            return ['ok' => false, 'message' => 'No Calendly scheduling URL has been set.'];
        }

        if (! filter_var($url, FILTER_VALIDATE_URL) || ! str_contains($url, 'calendly.com')) {
            return ['ok' => false, 'message' => 'That does not look like a Calendly scheduling link.'];
        }

        return ['ok' => true, 'message' => 'Calendly link looks valid: ' . $url];
    }

    /**
     * Dates the visitor can pick from, with at least one open slot each.
     *
     * @return array<int, array{date: string, label: string, weekday: string, slots: int}>
     */
    public function availableDates(int $limit = 14): array
    {
        if (! $this->isGoogle() || ! $this->google->isConfigured()) {
            return [];
        }

        $tz = $this->google->timezone();
        $cursor = Carbon::now($tz)->startOfDay();
        $end = $cursor->copy()->addDays($this->google->bookingWindowDays());
        $dates = [];

        while ($cursor->lessThanOrEqualTo($end) && count($dates) < $limit) {
            try {
                $slots = $this->google->availableSlots($cursor);
            } catch (Throwable $e) {
                // Calendar unreachable: offer no dates rather than an error
                // page. The lead is already captured.
                report($e);

                return $dates;
            }

            if ($slots !== []) {
                $dates[] = [
                    'date' => $cursor->toDateString(),
                    'label' => $cursor->format('j M'),
                    'weekday' => $cursor->format('D'),
                    'slots' => count($slots),
                ];
            }

            $cursor->addDay();
        }

        return $dates;
    }

    /**
     * @return array<int, array{start: string, end: string, label: string}>
     */
    public function slotsFor(string $date): array
    {
        if (! $this->isGoogle() || ! $this->google->isConfigured()) {
            return [];
        }

        try {
            return $this->google->availableSlots(Carbon::parse($date, $this->google->timezone()));
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Book a concrete time for an appointment on the Google calendar and record
     * it on the appointment row.
     *
     * @return array{meet_url: ?string, html_link: ?string, event_id: string}
     */
    public function schedule(Appointment $appointment, Carbon $start): array
    {
        if (! $this->isGoogle() || ! $this->google->isConfigured()) {
            throw new RuntimeException('Google Calendar is not the active provider or is not configured.');
        }

        $tz = $this->google->timezone();
        $start = $start->copy()->setTimezone($tz);
        $end = $start->copy()->addMinutes($this->google->slotMinutes());

        $this->guardAgainstDoubleBooking($appointment, $start);

        $serviceName = $appointment->service?->name ?? 'Care consultation';

        $result = $this->google->upsertEvent([
            'summary' => 'WestHub: ' . $appointment->full_name . ' — ' . $serviceName,
            'description' => $this->describe($appointment),
            'location' => $this->locationLabel($appointment),
            'start' => $start,
            'end' => $end,
            'attendee_email' => $appointment->email,
            'attendee_name' => $appointment->full_name,
        ], data_get($appointment->meta, 'google_event_id'));

        $meta = array_merge((array) ($appointment->meta ?? []), array_filter([
            'provider' => self::GOOGLE,
            'google_event_id' => $result['id'],
            'google_event_link' => $result['html_link'],
            'google_meet_url' => $result['meet_url'],
            'timezone' => $tz,
        ], static fn ($value): bool => ! is_null($value)));

        $appointment->forceFill(array_filter([
            'scheduled_at' => $start->copy()->utc(),
            'end_time' => $end->copy()->utc(),
            'event_type_name' => $appointment->service?->name,
            'status' => $appointment->scheduled_at ? Appointment::STATUS_RESCHEDULED : Appointment::STATUS_CONFIRMED,
            'meta' => $meta,
        ], static fn ($value): bool => ! is_null($value)))->save();

        return [
            'event_id' => $result['id'],
            'html_link' => $result['html_link'],
            'meet_url' => $result['meet_url'],
        ];
    }

    /**
     * Remove the calendar event for a cancelled appointment. A Google outage is
     * reported but never allowed to block the cancellation itself.
     */
    public function cancel(Appointment $appointment): void
    {
        $eventId = data_get($appointment->meta, 'google_event_id');

        if (! $eventId) {
            return;
        }

        try {
            if ($this->google->isConfigured()) {
                $this->google->deleteEvent((string) $eventId);
            }
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * The calendar is the source of truth for availability, but two visitors can
     * still pick the same slot in the same second. This is the last guard.
     */
    protected function guardAgainstDoubleBooking(Appointment $appointment, Carbon $start): void
    {
        $taken = Appointment::query()
            ->whereKeyNot($appointment->getKey())
            ->whereIn('status', Appointment::PENDING_STATUSES)
            ->where('scheduled_at', $start->copy()->utc())
            ->exists();

        if ($taken) {
            throw new SlotUnavailableException('That time has just been taken. Please choose another.');
        }
    }

    protected function describe(Appointment $appointment): string
    {
        return collect([
            'Name: ' . $appointment->full_name,
            'Email: ' . $appointment->email,
            $appointment->phone ? 'Phone: ' . $appointment->phone : null,
            $appointment->service?->name ? 'Service: ' . $appointment->service->name : null,
            data_get($appointment->meta, 'promo_code') ? 'Promo voucher: ' . data_get($appointment->meta, 'promo_code') : null,
            $appointment->message ? 'Message: ' . $appointment->message : null,
            'Source: ' . ($appointment->source ?? 'website'),
        ])->filter()->implode("\n");
    }

    protected function locationLabel(Appointment $appointment): ?string
    {
        $township = $appointment->township?->name ?? data_get($appointment->meta, 'location_preference.township_name');
        $county = $appointment->county?->name ?? data_get($appointment->meta, 'location_preference.county_name');

        $parts = array_filter([$township, $county]);

        return $parts === [] ? null : implode(', ', $parts);
    }
}
