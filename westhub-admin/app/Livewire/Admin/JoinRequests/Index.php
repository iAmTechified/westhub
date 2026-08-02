<?php

namespace App\Livewire\Admin\JoinRequests;

use App\Livewire\Admin\Concerns\InteractsWithAdminToast;
use App\Mail\JoinRequestContactMail;
use App\Models\JoinRequest;
use App\Models\JoinRequestEvent;
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
    public string $profession = 'all';
    public string $search = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public ?int $activeRequestId = null;
    public array $filteredRequestIds = [];
    public ?int $processingRequestId = null;
    public ?string $processingAction = null;

    // Email Modal State
    public bool $showEmailModal = false;
    public string $emailTo = '';
    public string $emailSubject = '';
    public string $emailBody = '';
    public ?int $emailRequestId = null;

    // File Viewer State
    public ?string $activeFilePath = null;
    public ?string $activeFileType = null;
    public bool $showFileViewer = false;
    public int $activeFileIndex = 0;

    protected array $allowedSortFields = [
        'created_at',
        'full_name',
        'status',
        'professional_type',
        'reviewed_at',
    ];

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function updatedProfession(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(string $sortBy): void
    {
        if (! in_array($sortBy, $this->allowedSortFields, true)) {
            $this->sortBy = 'created_at';
        }
    }

    public function updatedSortDirection(string $sortDirection): void
    {
        $this->sortDirection = $sortDirection === 'asc' ? 'asc' : 'desc';
    }

    public function openPreview(int $id): void
    {
        $this->activeRequestId = $id;
        $this->closeFileViewer();
    }

    public function closePreview(): void
    {
        $this->activeRequestId = null;
        $this->closeFileViewer();
    }

    public function setProfession(string $profession): void
    {
        $this->profession = $profession;
        $this->resetPage();
    }

    public function setSort(string $field): void
    {
        if (! in_array($field, $this->allowedSortFields, true)) {
            return;
        }

        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            return;
        }

        $this->sortBy = $field;
        $this->sortDirection = in_array($field, ['full_name', 'first_name', 'last_name', 'profession', 'status'], true) ? 'asc' : 'desc';
    }

    public function resetFilters(): void
    {
        $this->profession = 'all';
        $this->search = '';
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function goToPrevious(): void
    {
        if ($this->activeRequestId === null) {
            return;
        }

        $currentIndex = array_search($this->activeRequestId, $this->filteredRequestIds, true);
        if ($currentIndex === false || $currentIndex === 0) {
            return;
        }

        $this->activeRequestId = $this->filteredRequestIds[$currentIndex - 1];
        $this->closeFileViewer();
    }

    public function goToNext(): void
    {
        if ($this->activeRequestId === null) {
            return;
        }

        $currentIndex = array_search($this->activeRequestId, $this->filteredRequestIds, true);
        if ($currentIndex === false) {
            return;
        }

        if (! isset($this->filteredRequestIds[$currentIndex + 1])) {
            return;
        }

        $this->activeRequestId = $this->filteredRequestIds[$currentIndex + 1];
        $this->closeFileViewer();
    }

    public function openEmailModal(int $id): void
    {
        $joinRequest = JoinRequest::query()->findOrFail($id);
        
        $this->emailRequestId = $id;
        $this->emailTo = $joinRequest->email;
        $this->emailSubject = 'Regarding your application with WestHub Healthcare';

        $message = [
            "Hello {$joinRequest->full_name},",
            '',
            'Thank you for your interest in joining WestHub Healthcare.',
            'This is a follow-up regarding your application for the ' . ($joinRequest->position_applied_for ?: 'position') . '.',
            '',
            'If you have any questions, simply reply to this email and our team will assist you.',
            '',
            'Warm regards,',
            'WestHub Healthcare Recruitment Team',
        ];

        $this->emailBody = implode("\n", $message);
        $this->showEmailModal = true;
    }

    public function closeEmailModal(): void
    {
        $this->showEmailModal = false;
        $this->reset(['emailTo', 'emailSubject', 'emailBody', 'emailRequestId']);
    }

    public function sendEmail(): void
    {
        $this->validate([
            'emailSubject' => 'required|string|max:255',
            'emailBody' => 'required|string|min:12',
            'emailRequestId' => 'required|exists:join_requests,id',
        ]);

        $joinRequest = JoinRequest::query()->findOrFail($this->emailRequestId);
        $this->assertSmtpReady();

        try {
            Mail::to($joinRequest->email)->send(new JoinRequestContactMail(
                $this->emailSubject,
                $this->emailBody,
                $joinRequest->full_name
            ));

            OutboundMessage::create([
                'join_request_id' => $joinRequest->id,
                'recipient_email' => $joinRequest->email,
                'subject' => $this->emailSubject,
                'body' => $this->emailBody,
                'template_key' => 'join_request_contact',
                'provider' => config('mail.default'),
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $this->toastSuccess('Email sent successfully.', 'Email Sent');
            $this->closeEmailModal();
        } catch (\Throwable $e) {
            OutboundMessage::create([
                'join_request_id' => $joinRequest->id,
                'recipient_email' => $joinRequest->email,
                'subject' => $this->emailSubject,
                'body' => $this->emailBody,
                'template_key' => 'join_request_contact',
                'provider' => config('mail.default'),
                'status' => 'failed',
                'provider_response' => ['error' => $e->getMessage()],
                'failed_at' => now(),
            ]);

            $this->addError('emailBody', 'Failed to send email. Please check SMTP settings and try again.');
        }
    }

    public function openFileViewer(string $path, int $index = 0): void
    {
        $this->activeFilePath = $path;
        $this->activeFileIndex = $index;
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        $this->activeFileType = match ($extension) {
            'pdf' => 'pdf',
            'jpg', 'jpeg', 'png', 'webp' => 'image',
            default => 'other',
        };
        
        $this->showFileViewer = true;
    }

    public function closeFileViewer(): void
    {
        $this->showFileViewer = false;
        $this->activeFilePath = null;
        $this->activeFileType = null;
    }

    protected function assertSmtpReady(): void
    {
        $driver = config('mail.default');

        if (in_array($driver, ['log', 'array'], true)) {
            throw ValidationException::withMessages([
                'emailBody' => 'Email is not configured for this environment. Set MAIL_MAILER, MAIL_HOST, and credentials in your .env file before sending.',
            ]);
        }

        if ($driver !== 'smtp') {
            return;
        }

        $required = [
            'MAIL_HOST'         => config('mail.mailers.smtp.host'),
            'MAIL_PORT'         => config('mail.mailers.smtp.port'),
            'MAIL_FROM_ADDRESS' => config('mail.from.address'),
            'MAIL_FROM_NAME'    => config('mail.from.name'),
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
        $query = JoinRequest::query();
        $searchTerm = trim($this->search);

        if ($this->profession !== 'all') {
            $query->where('professional_type', $this->profession);
        }

        if ($searchTerm !== '') {
            $query->where(function ($builder) use ($searchTerm) {
                $builder->where('full_name', 'like', "%{$searchTerm}%")
                    ->orWhere('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('phone', 'like', "%{$searchTerm}%")
                    ->orWhere('professional_type', 'like', "%{$searchTerm}%")
                    ->orWhere('profession', 'like', "%{$searchTerm}%")
                    ->orWhere('position_applied_for', 'like', "%{$searchTerm}%");

                if (ctype_digit($searchTerm)) {
                    $builder->orWhereKey((int) $searchTerm);
                }
            });
        }

        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $sortField = in_array($this->sortBy, $this->allowedSortFields, true)
            ? $this->sortBy
            : 'created_at';

        return $query->orderBy($sortField, $direction)->orderByDesc('id');
    }

    public function render()
    {
        $professions = JoinRequest::query()
            ->whereNotNull('professional_type')
            ->where('professional_type', '!=', '')
            ->distinct()
            ->orderBy('professional_type')
            ->pluck('professional_type');

        $joinRequests = null;
        $activeRequest = null;
        $canMovePrevious = false;
        $canMoveNext = false;

        if ($this->readyToLoad) {
            $query = $this->baseQuery();
            $this->filteredRequestIds = (clone $query)->pluck('id')->all();

            if ($this->activeRequestId !== null && ! in_array($this->activeRequestId, $this->filteredRequestIds, true)) {
                $this->activeRequestId = $this->filteredRequestIds[0] ?? null;
            }

            $joinRequests = $query->paginate(12);
            $activeRequest = ($this->activeRequestId !== null)
                ? JoinRequest::query()->find($this->activeRequestId)
                : null;

            if ($activeRequest) {
                $activeIndex = array_search($activeRequest->id, $this->filteredRequestIds, true);
                if ($activeIndex !== false) {
                    $canMovePrevious = $activeIndex > 0;
                    $canMoveNext = isset($this->filteredRequestIds[$activeIndex + 1]);
                }
            }
        } else {
            $joinRequests = new LengthAwarePaginator([], 0, 12);
            $joinRequests->setPath(request()->url());
        }

        return view('livewire.admin.join-requests.index', compact(
            'joinRequests',
            'activeRequest',
            'professions',
            'canMovePrevious',
            'canMoveNext'
        ))
            ->layout('layouts.admin');
    }
}
