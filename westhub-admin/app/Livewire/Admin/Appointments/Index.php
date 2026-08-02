<?php

namespace App\Livewire\Admin\Appointments;

use App\Livewire\Admin\Concerns\InteractsWithAdminToast;
use App\Mail\AppointmentContactMail;
use App\Models\Appointment;
use App\Models\OutboundMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use InteractsWithAdminToast;

    protected $paginationView = 'livewire.admin-pagination';

    public bool $readyToLoad = true;
    public string $search = '';
    public string $scheduledDate = '';
    public ?int $activeAppointmentId = null;
    // Email Modal State
    public bool $showEmailModal = false;
    public string $emailTo = '';
    public string $emailSubject = '';
    public string $emailBody = '';
    public ?int $emailAppointmentId = null;

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedScheduledDate(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->scheduledDate = '';
        $this->resetPage();
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return trim($this->search) !== ''
            || $this->scheduledDate !== '';
    }

    public function openDetails(int $appointmentId): void
    {
        $this->activeAppointmentId = $appointmentId;
        $this->resetErrorBag();
    }

    public function closeDetails(): void
    {
        $this->activeAppointmentId = null;
    }

    public function openEmailModal(int $id): void
    {
        $appointment = Appointment::query()->findOrFail($id);
        $eventName = $appointment->event_type_name ?: ($appointment->service?->name ?: 'care appointment');
        $scheduledAt = $appointment->scheduled_at
            ? $appointment->scheduled_at->format('D, d M Y \a\t H:i')
            : null;

        $this->emailAppointmentId = $id;
        $this->emailTo = $appointment->email;
        $this->emailSubject = 'Regarding your WestHub appointment';

        $message = [
            "Hello {$appointment->full_name},",
            '',
            'Thank you for booking with WestHub Healthcare.',
            'This is a follow-up regarding your appointment request.',
            '',
            "Appointment type: {$eventName}",
        ];

        if ($scheduledAt) {
            $message[] = "Scheduled time: {$scheduledAt}";
        }

        $message = array_merge($message, [
            '',
            'If you have any questions, simply reply to this email and our team will assist you.',
            '',
            'Warm regards,',
            'WestHub Healthcare Team',
        ]);

        $this->emailBody = implode("\n", $message);
        $this->showEmailModal = true;
    }

    public function closeEmailModal(): void
    {
        $this->showEmailModal = false;
        $this->reset(['emailTo', 'emailSubject', 'emailBody', 'emailAppointmentId']);
    }

    public function sendEmail(): void
    {
        $this->validate([
            'emailSubject' => 'required|string|max:255',
            'emailBody' => 'required|string|min:12',
            'emailAppointmentId' => 'required|exists:appointments,id',
        ]);

        $appointment = Appointment::query()->findOrFail($this->emailAppointmentId);
        $this->assertSmtpReady();

        try {
            $fromEmail = \App\Support\SiteSettings::appointmentsEmail();
            $fromName = \App\Support\SiteSettings::fromName();

            $mailable = new AppointmentContactMail(
                $this->emailSubject,
                $this->emailBody,
                $appointment->full_name
            );

            if (filled($fromEmail)) {
                $mailable->from($fromEmail, $fromName);
            }

            Mail::to($appointment->email)->send($mailable);

            OutboundMessage::create([
                'appointment_id' => $appointment->id,
                'recipient_email' => $appointment->email,
                'subject' => $this->emailSubject,
                'body' => $this->emailBody,
                'template_key' => 'appointment_contact',
                'provider' => config('mail.default'),
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $this->toastSuccess('Email sent successfully.', 'Email Sent');
            $this->closeEmailModal();
        } catch (\Throwable $e) {
            OutboundMessage::create([
                'appointment_id' => $appointment->id,
                'recipient_email' => $appointment->email,
                'subject' => $this->emailSubject,
                'body' => $this->emailBody,
                'template_key' => 'appointment_contact',
                'provider' => config('mail.default'),
                'status' => 'failed',
                'provider_response' => ['error' => $e->getMessage()],
                'failed_at' => now(),
            ]);

            $this->addError('emailBody', 'Failed to send email. Please check SMTP settings and try again.');
        }
    }

    protected function assertSmtpReady(): void
    {
        $driver = config('mail.default');

        // Log/array drivers silently discard emails — block them early.
        if (in_array($driver, ['log', 'array'], true)) {
            throw ValidationException::withMessages([
                'emailBody' => 'Email is not configured for this environment. Set MAIL_MAILER, MAIL_HOST, and credentials in your .env file before sending.',
            ]);
        }

        if ($driver !== 'smtp') {
            return;
        }

        $fromAddress = \App\Support\SiteSettings::appointmentsEmail() ?: config('mail.from.address');
        $fromName = \App\Support\SiteSettings::fromName() ?: config('mail.from.name');

        $required = [
            'MAIL_HOST'         => config('mail.mailers.smtp.host'),
            'MAIL_PORT'         => config('mail.mailers.smtp.port'),
            'MAIL_FROM_ADDRESS' => $fromAddress,
            'MAIL_FROM_NAME'    => $fromName,
        ];

        $missing = collect($required)
            ->filter(fn ($value) => blank($value))
            ->keys()
            ->values()
            ->all();

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'emailBody' => 'SMTP is not fully configured. Missing: ' . implode(', ', $missing),
            ]);
        }
    }

    protected function baseQuery(): Builder
    {
        $query = Appointment::query()->with(['service', 'county', 'township']);

        if ($this->scheduledDate !== '') {
            $query->whereDate('scheduled_at', $this->scheduledDate);
        }

        if ($this->search !== '') {
            $search = trim($this->search);
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('event_type_name', 'like', "%{$search}%")
                    ->orWhereHas('service', fn (Builder $relation) => $relation->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->latest('created_at');
    }

    public function render()
    {
        $appointments = null;
        $activeAppointment = null;

        if ($this->readyToLoad) {
            $appointments = $this->baseQuery()->paginate(12);
            $activeAppointment = $this->activeAppointmentId
                ? Appointment::query()->with(['service', 'county', 'township'])->find($this->activeAppointmentId)
                : null;
        } else {
            $appointments = new LengthAwarePaginator([], 0, 12);
            $appointments->setPath(request()->url());
        }

        return view('livewire.admin.appointments.index', compact('appointments', 'activeAppointment'))
            ->layout('layouts.admin');
    }
}
