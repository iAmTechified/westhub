<?php

namespace Tests\Feature;

use App\Exceptions\Appointments\SlotUnavailableException;
use App\Jobs\Appointments\SyncAppointmentWithGoogleCalendar;
use App\Models\Appointment;
use App\Services\Appointments\AppointmentScheduler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class AppointmentsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduler_prevents_dual_booking_for_same_slot(): void
    {
        $scheduler = app(AppointmentScheduler::class);

        $first = Appointment::query()->create([
            'full_name' => 'Alex Carter',
            'email' => 'alex@example.com',
            'status' => Appointment::STATUS_NEW,
        ]);

        $second = Appointment::query()->create([
            'full_name' => 'Morgan Lee',
            'email' => 'morgan@example.com',
            'status' => Appointment::STATUS_NEW,
        ]);

        $slot = Carbon::parse('2026-05-01 10:00:00', 'UTC');
        $scheduler->schedule($first, $slot);

        $this->expectException(SlotUnavailableException::class);
        $scheduler->schedule($second, $slot);
    }

    public function test_scheduler_dispatches_calendar_sync_on_schedule_and_cancel(): void
    {
        Bus::fake();

        $scheduler = app(AppointmentScheduler::class);
        $appointment = Appointment::query()->create([
            'full_name' => 'Jamie Stone',
            'email' => 'jamie@example.com',
            'status' => Appointment::STATUS_NEW,
        ]);

        $slot = Carbon::parse('2026-05-01 11:30:00', 'UTC');
        $scheduler->schedule($appointment, $slot);

        Bus::assertDispatched(SyncAppointmentWithGoogleCalendar::class, function (SyncAppointmentWithGoogleCalendar $job) use ($appointment) {
            return $job->appointmentId === $appointment->id && $job->action === 'upsert';
        });

        $scheduler->cancel($appointment->fresh());

        Bus::assertDispatched(SyncAppointmentWithGoogleCalendar::class, function (SyncAppointmentWithGoogleCalendar $job) use ($appointment) {
            return $job->appointmentId === $appointment->id && $job->action === 'delete';
        });

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => Appointment::STATUS_CANCELLED,
        ]);
    }
}

