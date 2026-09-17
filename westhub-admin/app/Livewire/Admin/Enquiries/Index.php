<?php

namespace App\Livewire\Admin\Enquiries;

use Illuminate\Support\Facades\Gate;
use App\Models\Appointment;
use App\Models\AppointmentEvent;
use App\Models\County;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $readyToLoad = true;
    public string $search = '';
    public string $status = 'all';
    public string $source = 'all';
    public string $county = 'all';
    public string $service = 'all';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    public ?int $activeEnquiryId = null;
    public array $filteredEnquiryIds = [];
    public ?int $processingEnquiryId = null;
    public ?string $processingAction = null;

    public function mount(): void
    {
        Gate::authorize('enquiries.view');
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSource(): void
    {
        $this->resetPage();
    }

    public function updatedCounty(): void
    {
        $this->resetPage();
    }

    public function updatedService(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = 'all';
        $this->source = 'all';
        $this->county = 'all';
        $this->service = 'all';
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function setSort(string $field): void
    {
        $allowedFields = ['created_at', 'full_name', 'status', 'source', 'preferred_date'];
        if (! in_array($field, $allowedFields, true)) {
            return;
        }

        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            return;
        }

        $this->sortBy = $field;
        $this->sortDirection = in_array($field, ['full_name', 'status', 'source'], true) ? 'asc' : 'desc';
    }

    public function openPreview(int $id): void
    {
        $this->activeEnquiryId = $id;
    }

    public function closePreview(): void
    {
        $this->activeEnquiryId = null;
    }

    public function goToPrevious(): void
    {
        if ($this->activeEnquiryId === null) {
            return;
        }

        $currentIndex = array_search($this->activeEnquiryId, $this->filteredEnquiryIds, true);
        if ($currentIndex === false || $currentIndex === 0) {
            return;
        }

        $this->activeEnquiryId = $this->filteredEnquiryIds[$currentIndex - 1];
    }

    public function goToNext(): void
    {
        if ($this->activeEnquiryId === null) {
            return;
        }

        $currentIndex = array_search($this->activeEnquiryId, $this->filteredEnquiryIds, true);
        if ($currentIndex === false || ! isset($this->filteredEnquiryIds[$currentIndex + 1])) {
            return;
        }

        $this->activeEnquiryId = $this->filteredEnquiryIds[$currentIndex + 1];
    }

    public function assignToMe(int $id): void
    {
        Gate::authorize('enquiries.manage');

        $this->processingEnquiryId = $id;
        $this->processingAction = 'assign';

        try {
            $enquiry = Appointment::query()->findOrFail($id);
            $oldAssignee = $enquiry->assigned_to;

            $enquiry->update(['assigned_to' => auth()->id()]);

            AppointmentEvent::create([
                'appointment_id' => $enquiry->id,
                'actor_id' => auth()->id(),
                'event_type' => 'assignment',
                'note' => 'Assigned to current admin.',
                'meta' => [
                    'old_assigned_to' => $oldAssignee,
                    'new_assigned_to' => auth()->id(),
                ],
                'event_at' => now(),
            ]);
        } finally {
            $this->processingEnquiryId = null;
            $this->processingAction = null;
        }
    }

    public function unassign(int $id): void
    {
        Gate::authorize('enquiries.manage');

        $this->processingEnquiryId = $id;
        $this->processingAction = 'unassign';

        try {
            $enquiry = Appointment::query()->findOrFail($id);
            $oldAssignee = $enquiry->assigned_to;

            $enquiry->update(['assigned_to' => null]);

            AppointmentEvent::create([
                'appointment_id' => $enquiry->id,
                'actor_id' => auth()->id(),
                'event_type' => 'assignment',
                'note' => 'Unassigned from enquiry.',
                'meta' => [
                    'old_assigned_to' => $oldAssignee,
                    'new_assigned_to' => null,
                ],
                'event_at' => now(),
            ]);
        } finally {
            $this->processingEnquiryId = null;
            $this->processingAction = null;
        }
    }

    public function markConfirmed(int $id): void
    {
        Gate::authorize('enquiries.manage');

        $this->transition($id, Appointment::STATUS_CONFIRMED, 'Enquiry marked as confirmed.');
    }

    public function markRescheduled(int $id): void
    {
        Gate::authorize('enquiries.manage');

        $this->transition($id, Appointment::STATUS_RESCHEDULED, 'Enquiry marked as rescheduled.');
    }

    public function markCompleted(int $id): void
    {
        Gate::authorize('enquiries.manage');

        $this->transition($id, Appointment::STATUS_COMPLETED, 'Enquiry marked as completed.');
    }

    public function markCancelled(int $id): void
    {
        Gate::authorize('enquiries.manage');

        $this->transition($id, Appointment::STATUS_CANCELLED, 'Enquiry marked as cancelled.');
    }

    protected function transition(int $id, string $newStatus, string $note): void
    {
        $allowedStatuses = [
            Appointment::STATUS_NEW,
            Appointment::STATUS_CONFIRMED,
            Appointment::STATUS_RESCHEDULED,
            Appointment::STATUS_COMPLETED,
            Appointment::STATUS_CANCELLED,
        ];

        if (! in_array($newStatus, $allowedStatuses, true)) {
            return;
        }

        $this->processingEnquiryId = $id;
        $this->processingAction = $newStatus;

        try {
            $enquiry = Appointment::query()->findOrFail($id);
            $oldStatus = $enquiry->status;

            if ($oldStatus === $newStatus) {
                return;
            }

            $enquiry->update([
                'status' => $newStatus,
                'resolved_at' => in_array($newStatus, [Appointment::STATUS_COMPLETED, Appointment::STATUS_CANCELLED], true)
                    ? now()
                    : null,
            ]);

            AppointmentEvent::create([
                'appointment_id' => $enquiry->id,
                'actor_id' => auth()->id(),
                'event_type' => 'status_transition',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'note' => $note,
                'event_at' => now(),
            ]);
        } finally {
            $this->processingEnquiryId = null;
            $this->processingAction = null;
        }
    }

    protected function baseQuery(): Builder
    {
        $query = Appointment::query()->with(['service', 'county', 'township', 'assignee']);

        if ($this->search !== '') {
            $query->where(function (Builder $builder) {
                $builder->where('full_name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('message', 'like', "%{$this->search}%");
            });
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->source !== 'all') {
            $query->where('source', $this->source);
        }

        if ($this->county !== 'all') {
            $query->where('county_id', (int) $this->county);
        }

        if ($this->service !== 'all') {
            $query->where('service_id', (int) $this->service);
        }

        $direction = $this->sortDirection === 'asc' ? 'asc' : 'desc';
        $sortField = in_array($this->sortBy, ['created_at', 'full_name', 'status', 'source', 'preferred_date'], true)
            ? $this->sortBy
            : 'created_at';

        return $query->orderBy($sortField, $direction)->orderByDesc('id');
    }

    public function render()
    {
        $counties = collect();
        $services = collect();
        $sources = collect();

        $enquiries = new LengthAwarePaginator([], 0, 12);
        $enquiries->setPath(request()->url());
        $activeEnquiry = null;
        $canMovePrevious = false;
        $canMoveNext = false;

        if ($this->readyToLoad) {
            $counties = County::query()->orderBy('name')->get(['id', 'name']);
            $services = Service::query()->orderBy('name')->get(['id', 'name']);
            $sources = Appointment::query()->select('source')->whereNotNull('source')->distinct()->orderBy('source')->pluck('source');

            $query = $this->baseQuery();
            $this->filteredEnquiryIds = (clone $query)->pluck('id')->all();

            if ($this->activeEnquiryId !== null && ! in_array($this->activeEnquiryId, $this->filteredEnquiryIds, true)) {
                $this->activeEnquiryId = $this->filteredEnquiryIds[0] ?? null;
            }

            $enquiries = $query->paginate(12);
            $activeEnquiry = ($this->activeEnquiryId !== null)
                ? Appointment::query()
                    ->with([
                        'service',
                        'county',
                        'township',
                        'assignee',
                        'events.actor',
                    ])
                    ->find($this->activeEnquiryId)
                : null;

            if ($activeEnquiry) {
                $activeIndex = array_search($activeEnquiry->id, $this->filteredEnquiryIds, true);
                if ($activeIndex !== false) {
                    $canMovePrevious = $activeIndex > 0;
                    $canMoveNext = isset($this->filteredEnquiryIds[$activeIndex + 1]);
                }
            }
        }

        return view('livewire.admin.enquiries.index', compact(
            'enquiries',
            'activeEnquiry',
            'counties',
            'services',
            'sources',
            'canMovePrevious',
            'canMoveNext'
        ))->layout('layouts.admin');
    }
}

