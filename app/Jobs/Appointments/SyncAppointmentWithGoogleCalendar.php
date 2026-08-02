<?php

namespace App\Jobs\Appointments;

use App\Models\Appointment;
use App\Services\Appointments\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncAppointmentWithGoogleCalendar implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $appointmentId,
        public string $action = 'upsert',
    ) {
    }

    public function handle(GoogleCalendarService $calendar): void
    {
        $appointment = Appointment::query()->with(['county', 'township', 'service'])->find($this->appointmentId);
        if (! $appointment || ! $calendar->isConfigured()) {
            return;
        }

        try {
            if ($this->action === 'delete') {
                $calendar->deleteEvent($appointment);

                $meta = (array) ($appointment->meta ?? []);
                unset($meta['google_event_id']);
                $appointment->update(['meta' => $meta]);

                return;
            }

            $eventId = $calendar->upsertEvent($appointment);
            if ($eventId) {
                $meta = (array) ($appointment->meta ?? []);
                $meta['google_event_id'] = $eventId;
                $appointment->update(['meta' => $meta]);
            }
        } catch (\Throwable $exception) {
            Log::error('Appointment Google Calendar sync failed.', [
                'appointment_id' => $this->appointmentId,
                'action' => $this->action,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
