<?php

namespace App\Livewire\Admin\Subscribers;

use Illuminate\Support\Facades\Gate;
use App\Livewire\Admin\Concerns\InteractsWithAdminToast;
use App\Mail\Admin\NewsletterMail;
use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Builder;
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
    public string $status = 'all';
    public string $search = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    // Newsletter Modal State
    public bool $showNewsletterModal = false;
    public string $newsletterSubject = '';
    public string $newsletterBody = '';
    public bool $sendToAll = true;

    protected array $allowedSortFields = [
        'created_at',
        'email',
        'full_name',
        'status',
        'source',
        'subscribed_at',
    ];

    public function mount(): void
    {
        Gate::authorize('subscribers.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
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
        $this->sortDirection = in_array($field, ['email', 'full_name', 'status'], true) ? 'asc' : 'desc';
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function resetFilters(): void
    {
        $this->status = 'all';
        $this->search = '';
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        Gate::authorize('subscribers.manage');

        $subscriber = Subscriber::query()->findOrFail($id);
        $subscriber->status = $subscriber->status === Subscriber::STATUS_SUBSCRIBED
            ? Subscriber::STATUS_UNSUBSCRIBED
            : Subscriber::STATUS_SUBSCRIBED;

        if ($subscriber->status === Subscriber::STATUS_UNSUBSCRIBED) {
            $subscriber->unsubscribed_at = now();
        } else {
            $subscriber->unsubscribed_at = null;
            $subscriber->subscribed_at = $subscriber->subscribed_at ?: now();
        }

        $subscriber->save();

        $this->toastSuccess(
            "Subscriber {$subscriber->email} is now {$subscriber->status}.",
            'Status Updated'
        );
    }

    public function deleteSubscriber(int $id): void
    {
        Gate::authorize('subscribers.manage');

        $subscriber = Subscriber::query()->findOrFail($id);
        $email = $subscriber->email;
        $subscriber->delete();

        $this->toastSuccess("Subscriber {$email} has been removed.", 'Subscriber Deleted');
    }

    public function exportCsv()
    {
        Gate::authorize('subscribers.export');

        $subscribers = $this->baseQuery()->get();
        $filename = 'subscribers-' . now()->format('Y-m-d-His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($subscribers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Email', 'Full Name', 'Status', 'Source', 'Subscribed At', 'Unsubscribed At']);

            foreach ($subscribers as $subscriber) {
                fputcsv($file, [
                    $subscriber->id,
                    $subscriber->email,
                    $subscriber->full_name,
                    $subscriber->status,
                    $subscriber->source,
                    $subscriber->subscribed_at?->format('Y-m-d H:i:s'),
                    $subscriber->unsubscribed_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function openNewsletterModal(): void
    {
        Gate::authorize('subscribers.send');

        $this->newsletterSubject = 'Update from WestHub Healthcare';
        $this->showNewsletterModal = true;
    }

    public function closeNewsletterModal(): void
    {
        $this->showNewsletterModal = false;
        $this->reset(['newsletterSubject', 'newsletterBody']);
    }

    public function sendNewsletter(): void
    {
        Gate::authorize('subscribers.send');

        $this->validate([
            'newsletterSubject' => 'required|string|max:255',
            'newsletterBody' => 'required|string|min:20',
        ]);

        $subscribers = Subscriber::query()
            ->where('status', Subscriber::STATUS_SUBSCRIBED)
            ->get();

        if ($subscribers->isEmpty()) {
            $this->addError('newsletterBody', 'There are no active subscribers to send to.');
            return;
        }

        try {
            $this->assertSmtpReady();

            $fromEmail = \App\Support\SiteSettings::newsletterEmail();
            $fromName = \App\Support\SiteSettings::fromName();

            $queuedCount = 0;
            foreach ($subscribers as $subscriber) {
                $mailable = new NewsletterMail(
                    $this->newsletterSubject,
                    $this->newsletterBody,
                    $subscriber->full_name ?: 'Valued Subscriber',
                    $subscriber->email
                );

                if (filled($fromEmail)) {
                    $mailable->from($fromEmail, $fromName);
                }

                Mail::to($subscriber->email)->queue($mailable);
                $queuedCount++;
            }

            $driver = config('mail.default');
            $message = $driver === 'log' 
                ? "Broadcast logged for {$queuedCount} subscribers (Log Driver Active)."
                : "Newsletter has been queued for {$queuedCount} subscribers.";

            $this->toastSuccess($message, 'Newsletter Sent');
            $this->closeNewsletterModal();
        } catch (\Throwable $e) {
            $this->toastError('Failed to process newsletter: ' . $e->getMessage());
            $this->addError('newsletterBody', 'Error: ' . $e->getMessage());
        }
    }

    protected function assertSmtpReady(): void
    {
        $driver = config('mail.default');
        if (in_array($driver, ['log', 'array'], true)) {
            return;
        }

        $fromAddress = \App\Support\SiteSettings::newsletterEmail() ?: config('mail.from.address');
        $required = [
            'MAIL_HOST' => config('mail.mailers.smtp.host'),
            'MAIL_FROM_ADDRESS' => $fromAddress,
        ];

        foreach ($required as $key => $value) {
            if (blank($value)) {
                throw ValidationException::withMessages([
                    'newsletterBody' => "Email configuration missing: {$key}. Please check your .env settings.",
                ]);
            }
        }
    }

    protected function baseQuery(): Builder
    {
        $query = Subscriber::query();
        $searchTerm = trim($this->search);

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($searchTerm !== '') {
            $query->where(function ($builder) use ($searchTerm) {
                $builder->where('email', 'like', "%{$searchTerm}%")
                    ->orWhere('full_name', 'like', "%{$searchTerm}%")
                    ->orWhere('source', 'like', "%{$searchTerm}%");
            });
        }

        return $query->orderBy($this->sortBy, $this->sortDirection);
    }

    public function render()
    {
        $subscribers = $this->readyToLoad
            ? $this->baseQuery()->paginate(15)
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);

        $stats = [
            'total' => Subscriber::count(),
            'active' => Subscriber::where('status', Subscriber::STATUS_SUBSCRIBED)->count(),
            'unsubscribed' => Subscriber::where('status', Subscriber::STATUS_UNSUBSCRIBED)->count(),
        ];

        return view('livewire.admin.subscribers.index', [
            'subscribers' => $subscribers,
            'stats' => $stats,
        ])->layout('layouts.admin');
    }
}
