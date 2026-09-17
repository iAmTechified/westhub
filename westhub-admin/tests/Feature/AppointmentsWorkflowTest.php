<?php

namespace Tests\Feature;

use App\Exceptions\Appointments\SlotUnavailableException;
use App\Models\Appointment;
use App\Services\Appointments\AppointmentProviderManager;
use App\Services\Google\GoogleCalendar;
use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use RuntimeException;
use Tests\TestCase;

/**
 * Covers AppointmentProviderManager, which replaced the old
 * AppointmentScheduler + SyncAppointmentWithGoogleCalendar job pair.
 *
 * The provider is chosen through the config fallback so these tests do not
 * depend on how the settings table connection is wired in the test database.
 */
class AppointmentsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SiteSettings::flush();
    }

    protected function tearDown(): void
    {
        SiteSettings::flush();

        parent::tearDown();
    }

    public function test_google_provider_writes_the_booking_to_the_calendar_and_invites_the_client(): void
    {
        $this->useProvider('google');
        $calendar = $this->fakeCalendar();

        $appointment = $this->makeAppointment('Jamie Stone', 'jamie@example.com');

        $result = app(AppointmentProviderManager::class)->schedule($appointment, Carbon::parse('2026-05-01 11:30:00', 'UTC'));

        $this->assertSame('evt_1', $result['event_id']);
        $this->assertCount(1, $calendar->upserts, 'The booking should have been written to the calendar.');
        $this->assertSame('jamie@example.com', $calendar->upserts[0]['attendee_email'], 'The client should be invited as an attendee.');

        $fresh = $appointment->fresh();

        $this->assertSame(Appointment::STATUS_CONFIRMED, $fresh->status);
        $this->assertSame('2026-05-01 11:30:00', $fresh->scheduled_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-05-01 12:30:00', $fresh->end_time->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('evt_1', data_get($fresh->meta, 'google_event_id'));
        $this->assertSame('google', data_get($fresh->meta, 'provider'));

        app(AppointmentProviderManager::class)->cancel($fresh);

        $this->assertSame(['evt_1'], $calendar->deletions, 'Cancelling should remove the calendar event.');
    }

    public function test_google_provider_prevents_double_booking_the_same_slot(): void
    {
        $this->useProvider('google');
        $this->fakeCalendar();

        $manager = app(AppointmentProviderManager::class);
        $slot = Carbon::parse('2026-05-01 10:00:00', 'UTC');

        $manager->schedule($this->makeAppointment('Alex Carter', 'alex@example.com'), $slot);

        $this->expectException(SlotUnavailableException::class);
        $manager->schedule($this->makeAppointment('Morgan Lee', 'morgan@example.com'), $slot);
    }

    public function test_calendly_provider_never_touches_google(): void
    {
        $this->useProvider('calendly');
        $calendar = $this->fakeCalendar();

        $manager = app(AppointmentProviderManager::class);
        $appointment = $this->makeAppointment('Robin Vale', 'robin@example.com');

        $this->assertFalse($manager->isGoogle());
        $this->assertSame([], $manager->availableDates());
        $this->assertSame([], $manager->slotsFor('2026-05-02'));

        try {
            $manager->schedule($appointment, Carbon::parse('2026-05-02 09:00:00', 'UTC'));
            $this->fail('Scheduling through Google should be refused while Calendly is the provider.');
        } catch (RuntimeException) {
            //
        }

        $this->assertSame([], $calendar->upserts);
        $this->assertSame(0, $calendar->availabilityChecks);
        $this->assertNull($appointment->fresh()->scheduled_at);
    }

    public function test_a_google_outage_does_not_throw_out_of_admin_paths(): void
    {
        $this->useProvider('google');

        $this->app->instance(GoogleCalendar::class, new class extends GoogleCalendar
        {
            public function isConfigured(): bool
            {
                return true;
            }

            public function timezone(): string
            {
                return 'UTC';
            }

            public function bookingWindowDays(): int
            {
                return 3;
            }

            public function availableSlots(Carbon $day): array
            {
                throw new RuntimeException('Google is down');
            }

            public function deleteEvent(string $eventId): void
            {
                throw new RuntimeException('Google is down');
            }
        });

        $manager = app(AppointmentProviderManager::class);

        $appointment = $this->makeAppointment('Sam Rivers', 'sam@example.com');
        $appointment->forceFill([
            'status' => Appointment::STATUS_CANCELLED,
            'meta' => ['google_event_id' => 'evt_gone'],
        ])->save();

        $manager->cancel($appointment->fresh());

        $this->assertSame([], $manager->availableDates());
        $this->assertSame([], $manager->slotsFor('2026-05-03'));
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_CANCELLED,
        ]);
    }

    protected function useProvider(string $provider): void
    {
        config()->set('services.appointments.provider', $provider);
        SiteSettings::flush();
    }

    protected function makeAppointment(string $name, string $email): Appointment
    {
        return Appointment::query()->create([
            'full_name' => $name,
            'email' => $email,
            'status' => Appointment::STATUS_NEW,
        ]);
    }

    protected function fakeCalendar(): GoogleCalendar
    {
        $fake = new class extends GoogleCalendar
        {
            public array $upserts = [];

            public array $deletions = [];

            public int $availabilityChecks = 0;

            public function isConfigured(): bool
            {
                return true;
            }

            public function timezone(): string
            {
                return 'UTC';
            }

            public function slotMinutes(): int
            {
                return 60;
            }

            public function availableSlots(Carbon $day): array
            {
                $this->availabilityChecks++;

                return [];
            }

            public function upsertEvent(array $details, ?string $existingEventId = null): array
            {
                $this->upserts[] = $details;

                return ['id' => 'evt_1', 'html_link' => null, 'meet_url' => null];
            }

            public function deleteEvent(string $eventId): void
            {
                $this->deletions[] = $eventId;
            }
        };

        $this->app->instance(GoogleCalendar::class, $fake);

        return $fake;
    }
}
