<div class="space-y-5" wire:init="loadData" x-data="{ showModal: false, isLoading: false }">
    <header class="glass-card p-6 lg:p-8">
        <p class="text-xs uppercase tracking-[0.22em] text-admin-muted">Operations</p>
        <h2 class="mt-2 inline-flex items-center gap-2 text-3xl font-semibold">
            <x-admin.icon name="mail" class="h-6 w-6 text-primary-100" />
            Enquiries
        </h2>
        <p class="mt-2 text-admin-muted">Track inbound care enquiries, assign ownership, and progress each request to resolution.</p>
    </header>

    <div class="glass-card p-4 md:p-5 space-y-4 relative z-50 overflow-visible">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1.4fr_.9fr_.9fr_.9fr_.9fr_auto] items-end">
            <div>
                <label class="admin-label">Search</label>
                <div class="admin-input-wrap relative">
                    <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-admin-muted" />
                    <input type="text" class="admin-input pl-9" placeholder="Name, email, msg..." wire:model.live.debounce.250ms="search">
                </div>
            </div>

            <div>
                <label class="admin-label">Status</label>
                <x-admin.select wire:model.live="status" placeholder="Filter status">
                    <x-admin.option value="all">All statuses</x-admin.option>
                    @foreach(['new', 'confirmed', 'rescheduled', 'completed', 'cancelled'] as $state)
                        <x-admin.option value="{{ $state }}">{{ str($state)->replace('_', ' ')->title() }}</x-admin.option>
                    @endforeach
                </x-admin.select>
            </div>

            <div>
                <label class="admin-label">Source</label>
                <x-admin.select wire:model.live="source" placeholder="Filter source">
                    <x-admin.option value="all">All sources</x-admin.option>
                    @foreach($sources as $sourceValue)
                        <x-admin.option value="{{ $sourceValue }}">{{ str($sourceValue)->replace('_', ' ')->title() }}</x-admin.option>
                    @endforeach
                </x-admin.select>
            </div>

            <div>
                <label class="admin-label">County</label>
                <x-admin.select wire:model.live="county" placeholder="Filter county">
                    <x-admin.option value="all">All counties</x-admin.option>
                    @foreach($counties as $countyOption)
                        <x-admin.option value="{{ $countyOption->id }}">{{ $countyOption->name }}</x-admin.option>
                    @endforeach
                </x-admin.select>
            </div>

            <div>
                <label class="admin-label">Service</label>
                <x-admin.select wire:model.live="service" placeholder="Filter service">
                    <x-admin.option value="all">All services</x-admin.option>
                    @foreach($services as $serviceOption)
                        <x-admin.option value="{{ $serviceOption->id }}">{{ $serviceOption->name }}</x-admin.option>
                    @endforeach
                </x-admin.select>
            </div>

            <button type="button" wire:click="resetFilters" class="admin-ghost-btn gap-2 whitespace-nowrap h-10">
                <x-admin.icon name="filter" class="h-4 w-4" />
                Reset
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs text-admin-muted">
            <span class="inline-flex items-center gap-1 rounded-full border border-admin-stroke px-3 py-1.5 bg-white/5">
                <x-admin.icon name="sort" class="h-3.5 w-3.5" />
                Sorted by {{ str($sortBy)->replace('_', ' ')->title() }} ({{ strtoupper($sortDirection) }})
            </span>
            <span class="inline-flex items-center gap-1 rounded-full border border-admin-stroke px-3 py-1.5 bg-white/5">
                <x-admin.icon name="mail" class="h-3.5 w-3.5" />
                Enquiry queue
            </span>
        </div>
    </div>

    @if(! $readyToLoad)
        <div class="glass-card p-4 md:p-5 space-y-3">
            <div class="admin-skeleton h-10"></div>
            <div class="admin-skeleton h-14"></div>
            <div class="admin-skeleton h-14"></div>
            <div class="admin-skeleton h-14"></div>
            <div class="admin-skeleton h-14"></div>
        </div>
    @else
        <div class="glass-card overflow-visible">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1020px] text-sm">
                    <thead class="bg-white/5 text-admin-muted">
                        <tr>
                            <th class="text-left p-3">
                                <button type="button" wire:click="setSort('full_name')" class="admin-table-head-btn">
                                    Contact
                                    <x-admin.icon name="sort" class="h-3.5 w-3.5" />
                                </button>
                            </th>
                            <th class="text-left p-3">Service / Location</th>
                            <th class="text-left p-3">
                                <button type="button" wire:click="setSort('status')" class="admin-table-head-btn">
                                    Status
                                    <x-admin.icon name="sort" class="h-3.5 w-3.5" />
                                </button>
                            </th>
                            <th class="text-left p-3">
                                <button type="button" wire:click="setSort('created_at')" class="admin-table-head-btn">
                                    Submitted
                                    <x-admin.icon name="sort" class="h-3.5 w-3.5" />
                                </button>
                            </th>
                            <th class="text-left p-3">Assignee</th>
                            <th class="text-right p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($enquiries as $enquiry)
                            @php
                                $isCurrentAction = $processingEnquiryId === $enquiry->id;
                                $isPending = \App\Models\Appointment::isPending($enquiry->status);
                            @endphp
                            <tr class="border-t border-admin-stroke hover:bg-white/5 transition-colors duration-[140ms]">
                                <td class="p-3">
                                    <p class="font-medium">{{ $enquiry->full_name }}</p>
                                    <p class="text-xs text-admin-muted">{{ $enquiry->email }}</p>
                                    <p class="text-xs text-admin-muted">{{ $enquiry->phone ?: 'No phone provided' }}</p>
                                </td>
                                <td class="p-3">
                                    <p>{{ $enquiry->service?->name ?: 'Service not selected' }}</p>
                                    <p class="text-xs text-admin-muted">
                                        {{ $enquiry->county?->name ?: 'County not selected' }}@if($enquiry->township), {{ $enquiry->township->name }}@endif
                                    </p>
                                </td>
                                <td class="p-3">
                                    <span class="admin-status-badge is-{{ $enquiry->status }}">{{ str($enquiry->status)->replace('_', ' ')->title() }}</span>
                                </td>
                                <td class="p-3 text-admin-muted">{{ optional($enquiry->created_at)->format('d M Y, H:i') }}</td>
                                <td class="p-3">
                                    <p class="text-sm">{{ $enquiry->assignee?->name ?: 'Unassigned' }}</p>
                                    <p class="text-xs text-admin-muted">{{ str($enquiry->source)->replace('_', ' ')->title() }}</p>
                                </td>
                                <td class="p-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button" @click="showModal = true; isLoading = true; $wire.openPreview({{ $enquiry->id }}).then(() => isLoading = false)" class="admin-icon-btn" title="View details">
                                            <x-admin.icon name="eye" class="h-4 w-4" />
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="assignToMe({{ $enquiry->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="assignToMe({{ $enquiry->id }})"
                                            class="admin-ghost-btn h-9 px-2"
                                            @disabled($isCurrentAction)
                                            title="Assign to me"
                                        >
                                            <span wire:loading.remove wire:target="assignToMe({{ $enquiry->id }})">Mine</span>
                                            <span wire:loading wire:target="assignToMe({{ $enquiry->id }})" class="tiny-orb-loader"></span>
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="markCompleted({{ $enquiry->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="markCompleted({{ $enquiry->id }})"
                                            class="admin-primary-btn h-9 w-9 p-0"
                                            @disabled(! $isPending || $isCurrentAction)
                                            title="Mark completed"
                                        >
                                            <span wire:loading.remove wire:target="markCompleted({{ $enquiry->id }})">
                                                <x-admin.icon name="check" class="h-4 w-4" />
                                            </span>
                                            <span wire:loading wire:target="markCompleted({{ $enquiry->id }})" class="tiny-orb-loader"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-10 text-center text-admin-muted">No enquiries match your current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $enquiries->links() }}</div>
        </div>
    @endif

    <div x-show="showModal" class="admin-modal-backdrop" @click="showModal = false; $wire.closePreview()" x-cloak></div>
    <section x-show="showModal" class="admin-modal-panel max-h-[90vh] overflow-y-auto" role="dialog" aria-modal="true" x-cloak>
        <div x-show="isLoading" class="flex flex-col gap-3">
            <div class="admin-skeleton h-8 w-1/2"></div>
            <div class="admin-skeleton h-16"></div>
            <div class="admin-skeleton h-16"></div>
            <div class="admin-skeleton h-32"></div>
        </div>

        <div x-show="!isLoading">
                @if($activeEnquiry)
                    @php
                        $isPending = \App\Models\Appointment::isPending($activeEnquiry->status);
                        $isCurrentAction = $processingEnquiryId === $activeEnquiry->id;
                    @endphp
                    <header class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-[0.18em] text-admin-muted">Care Enquiry</p>
                            <h3 class="mt-1 text-2xl font-semibold">{{ $activeEnquiry->full_name }}</h3>
                            <p class="mt-2 inline-flex items-center gap-2 text-sm text-admin-muted">
                                <x-admin.icon name="calendar" class="h-4 w-4" />
                                Submitted {{ optional($activeEnquiry->created_at)->format('d M Y, H:i') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="isLoading = true; $wire.goToPrevious().then(() => isLoading = false)" class="admin-modal-nav-btn {{ $canMovePrevious ? '' : 'is-disabled' }}" @disabled(! $canMovePrevious) title="Previous enquiry">
                                <x-admin.icon name="chevron-up" class="h-4 w-4" />
                                Prev
                            </button>
                            <button type="button" @click="isLoading = true; $wire.goToNext().then(() => isLoading = false)" class="admin-modal-nav-btn {{ $canMoveNext ? '' : 'is-disabled' }}" @disabled(! $canMoveNext) title="Next enquiry">
                                <x-admin.icon name="chevron-down" class="h-4 w-4" />
                                Next
                            </button>
                            <button type="button" @click="showModal = false; $wire.closePreview()" class="admin-icon-btn h-10 w-10" title="Close">
                                <x-admin.icon name="close" class="h-4 w-4" />
                            </button>
                        </div>
                    </header>

                    <div class="mt-5 grid gap-3 md:grid-cols-2">
                        <div class="rounded-xl border border-admin-stroke bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.12em] text-admin-muted">Contact</p>
                            <p class="mt-3 inline-flex items-center gap-2"><x-admin.icon name="mail" class="h-4 w-4" /> {{ $activeEnquiry->email }}</p>
                            <p class="mt-2 inline-flex items-center gap-2"><x-admin.icon name="phone" class="h-4 w-4" /> {{ $activeEnquiry->phone ?: 'Not provided' }}</p>
                        </div>
                        <div class="rounded-xl border border-admin-stroke bg-white/5 p-4">
                            <p class="text-xs uppercase tracking-[0.12em] text-admin-muted">Service Request</p>
                            <p class="mt-3 inline-flex items-center gap-2"><x-admin.icon name="briefcase" class="h-4 w-4" /> {{ $activeEnquiry->service?->name ?: 'Service not selected' }}</p>
                            <p class="mt-2 text-sm text-admin-muted">
                                {{ $activeEnquiry->county?->name ?: 'County not selected' }}@if($activeEnquiry->township), {{ $activeEnquiry->township->name }}@endif
                            </p>
                            <p class="mt-2"><span class="admin-status-badge is-{{ $activeEnquiry->status }}">{{ str($activeEnquiry->status)->replace('_', ' ')->title() }}</span></p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-admin-stroke bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.12em] text-admin-muted">Preferred Time</p>
                        <p class="mt-2 text-sm text-admin-muted">
                            @if($activeEnquiry->preferred_date)
                                {{ optional($activeEnquiry->preferred_date)->format('d M Y') }}
                            @else
                                Date not provided
                            @endif
                            @if($activeEnquiry->preferred_time)
                                at {{ $activeEnquiry->preferred_time }}
                            @endif
                        </p>
                    </div>

                    <div class="mt-4 rounded-xl border border-admin-stroke bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.12em] text-admin-muted">Message</p>
                        <p class="mt-2 whitespace-pre-line text-sm text-admin-muted">{{ $activeEnquiry->message ?: 'No additional message provided.' }}</p>
                    </div>

                    <div class="mt-4 rounded-xl border border-admin-stroke bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.12em] text-admin-muted">Assignment</p>
                        <p class="mt-2 text-sm text-admin-muted">
                            Assignee: {{ $activeEnquiry->assignee?->name ?: 'Unassigned' }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" wire:click="assignToMe({{ $activeEnquiry->id }})" class="admin-ghost-btn gap-2" @disabled($isCurrentAction)>
                                Assign to me
                            </button>
                            <button type="button" wire:click="unassign({{ $activeEnquiry->id }})" class="admin-ghost-btn gap-2" @disabled($isCurrentAction)>
                                Unassign
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-admin-stroke bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.12em] text-admin-muted">Activity Timeline</p>
                        <div class="mt-3 space-y-2">
                            @forelse($activeEnquiry->events as $event)
                                <div class="rounded-lg border border-admin-stroke bg-white/5 p-3">
                                    <p class="text-sm font-medium">{{ str($event->event_type)->replace('_', ' ')->title() }}</p>
                                    <p class="text-xs text-admin-muted mt-1">
                                        {{ optional($event->event_at)->format('d M Y, H:i') }} by {{ $event->actor?->name ?: 'System' }}
                                    </p>
                                    @if($event->old_status || $event->new_status)
                                        <p class="text-xs text-admin-muted mt-1">
                                            {{ $event->old_status ?: 'N/A' }} → {{ $event->new_status ?: 'N/A' }}
                                        </p>
                                    @endif
                                    @if($event->note)
                                        <p class="text-sm text-admin-muted mt-2">{{ $event->note }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-admin-muted">No activity has been logged for this enquiry yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <footer class="mt-5 flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="markConfirmed({{ $activeEnquiry->id }})" class="admin-primary-btn gap-2" @disabled(! $isPending || $isCurrentAction)>
                            Confirm
                        </button>
                        <button type="button" wire:click="markRescheduled({{ $activeEnquiry->id }})" class="admin-ghost-btn gap-2" @disabled(! $isPending || $isCurrentAction)>
                            Reschedule
                        </button>
                        <button type="button" wire:click="markCompleted({{ $activeEnquiry->id }})" class="admin-primary-btn gap-2" @disabled(! $isPending || $isCurrentAction)>
                            Complete
                        </button>
                        <button type="button" wire:click="markCancelled({{ $activeEnquiry->id }})" class="admin-ghost-btn gap-2" @disabled(! $isPending || $isCurrentAction)>
                            Cancel
                        </button>
                    </footer>
                @else
                    <p class="text-admin-muted">This enquiry is no longer available for the current filter set.</p>
                @endif
            </div>
        </section>
</div>



