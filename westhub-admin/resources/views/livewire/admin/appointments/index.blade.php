<div class="space-y-5" wire:init="loadData" x-data="{ showModal: false, isLoading: false }">
    <header class="glass-card p-6 lg:p-8">
        <p class="text-xs uppercase tracking-[0.22em] text-admin-muted">Operations</p>
        <h2 class="mt-2 inline-flex items-center gap-2 text-3xl font-semibold">
            <x-admin.icon name="calendar" class="h-6 w-6 text-primary-100" />
            Appointments
        </h2>
        <p class="mt-2 text-admin-muted">View client booking requests and Calendly-integrated sessions. This module is view-only.</p>
    </header>

    <div class="glass-card p-4 md:p-5 space-y-4 relative z-50 overflow-visible">
        <div class="grid gap-3 lg:grid-cols-[1.5fr_1fr_auto] items-end">
            <div>
                <label class="admin-label">Search</label>
                <div class="admin-input-wrap relative">
                    <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-admin-muted" />
                    <input type="text" class="admin-input pl-9" placeholder="Name, email, event type..." wire:model.live.debounce.250ms="search">
                </div>
            </div>

            <div>
                <label class="admin-label">Scheduled Date</label>
                <x-admin.date-picker wire:model.live="scheduledDate" placeholder="Pick scheduled date" />
            </div>

            @if($this->hasActiveFilters)
                <button type="button" wire:click="resetFilters" class="admin-ghost-btn gap-2 whitespace-nowrap h-10">
                    <x-admin.icon name="refresh" class="h-4 w-4" />
                    Reset
                </button>
            @endif
        </div>
    </div>

    @if(! $readyToLoad)
        <div class="glass-card p-4 md:p-5 space-y-3">
            <div class="admin-skeleton h-10"></div>
            <div class="admin-skeleton h-14"></div>
            <div class="admin-skeleton h-14"></div>
            <div class="admin-skeleton h-14"></div>
        </div>
    @else
        <div class="glass-card overflow-visible">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-sm">
                    <thead class="bg-white/5 text-admin-muted">
                        <tr>
                            <th class="text-left p-3">Client</th>
                            <th class="text-left p-3">Event / Service</th>
                            <th class="text-left p-3">Preferred</th>
                            <th class="text-left p-3">Scheduled</th>
                            <th class="text-right p-3">Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                            <tr
                                class="border-t border-admin-stroke hover:bg-white/5 transition-colors duration-[140ms] cursor-pointer"
                                @click="showModal = true; isLoading = true; $wire.openDetails({{ $appointment->id }}).then(() => isLoading = false)"
                                title="View appointment"
                            >
                                <td class="p-3">
                                    <p class="font-medium">{{ $appointment->full_name }}</p>
                                    <p class="text-xs text-admin-muted">{{ $appointment->email }}</p>
                                    <p class="text-xs text-admin-muted">{{ $appointment->phone ?: 'No phone' }}</p>
                                </td>
                                <td class="p-3">
                                    <span class="block">{{ $appointment->event_type_name ?: ($appointment->service?->name ?: 'General') }}</span>
                                    @if($appointment->calendly_event_id || $appointment->calendly_invitee_id || $appointment->reschedule_url || $appointment->cancel_url)
                                        <span class="text-[10px] uppercase tracking-wider text-indigo-400 font-semibold">Calendly</span>
                                    @endif
                                </td>
                                <td class="p-3 text-admin-muted">
                                    {{ $appointment->preferred_date?->format('d M Y') ?: 'N/A' }}
                                    @if($appointment->preferred_time)
                                        <span class="block text-xs">{{ $appointment->preferred_time }}</span>
                                    @endif
                                </td>
                                <td class="p-3 text-admin-muted">
                                    {{ $appointment->scheduled_at?->format('d M Y') ?: 'Not scheduled' }}
                                    @if($appointment->scheduled_at)
                                        <span class="block text-xs">{{ $appointment->scheduled_at->format('H:i') }} @if($appointment->end_time) - {{ $appointment->end_time->format('H:i') }} @endif</span>
                                    @endif
                                </td>
                                <td class="p-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button" @click.stop wire:click="openEmailModal({{ $appointment->id }})" class="admin-icon-btn" title="Send email">
                                            <x-admin.icon name="mail" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-10 text-center text-admin-muted">No appointments found for current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $appointments->links('livewire.admin-pagination') }}</div>
        </div>
    @endif

    {{-- Details Modal --}}
    <div x-show="showModal" class="admin-modal-backdrop" @click="showModal = false; $wire.closeDetails()" x-cloak></div>
    <section x-show="showModal" class="admin-modal-panel max-h-[90vh] overflow-y-auto" role="dialog" aria-modal="true" x-cloak>
        <div x-show="isLoading" class="flex flex-col items-center justify-center py-24 gap-4">
            <x-admin.icon name="refresh" class="h-8 w-8 animate-spin text-admin-muted" />
            <p class="text-admin-muted text-sm tracking-wide uppercase">Loading details...</p>
        </div>

        <div x-show="!isLoading">
            @if($activeAppointment)
                <header class="flex flex-wrap items-start justify-between gap-3 border-b border-admin-stroke pb-5 mb-6">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-admin-muted">Appointment Profile</p>
                        <h3 class="mt-1 text-2xl font-semibold">{{ $activeAppointment->full_name }}</h3>
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-admin-muted">
                            <span class="rounded-full border border-admin-stroke px-2.5 py-1">ID #{{ $activeAppointment->id }}</span>
                            <span class="rounded-full border border-admin-stroke px-2.5 py-1">
                                {{ $activeAppointment->event_type_name ?: ($activeAppointment->service?->name ?: 'General') }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="openEmailModal({{ $activeAppointment->id }})" class="admin-primary-btn h-10 px-4">
                            <x-admin.icon name="mail" class="h-4 w-4" />
                            <span>Email</span>
                        </button>
                        <button type="button" @click="showModal = false; $wire.closeDetails()" class="admin-icon-btn h-10 w-10" title="Close">
                            <x-admin.icon name="close" class="h-4 w-4" />
                        </button>
                    </div>
                </header>

                <div class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl border border-admin-stroke bg-white/5 p-4 space-y-3">
                            <p class="text-xs uppercase tracking-[0.12em] text-admin-muted font-bold">Contact</p>
                            <p class="inline-flex items-center gap-2 text-sm"><x-admin.icon name="mail" class="h-4 w-4 text-admin-muted" /> {{ $activeAppointment->email }}</p>
                            <p class="inline-flex items-center gap-2 text-sm"><x-admin.icon name="phone" class="h-4 w-4 text-admin-muted" /> {{ $activeAppointment->phone ?: 'Not provided' }}</p>
                            <p class="inline-flex items-center gap-2 text-sm"><x-admin.icon name="calendar" class="h-4 w-4 text-admin-muted" /> Submitted {{ optional($activeAppointment->created_at)->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="rounded-xl border border-admin-stroke bg-white/5 p-4 space-y-3">
                            <p class="text-xs uppercase tracking-[0.12em] text-admin-muted font-bold">Appointment Snapshot</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <div class="admin-data-pill">
                                        <span class="label">Preferred date</span>
                                        <span class="value">{{ $activeAppointment->preferred_date?->format('d M Y') ?: 'N/A' }}</span>
                                    </div>
                                    <div class="admin-data-pill">
                                        <span class="label">Preferred time</span>
                                        <span class="value">{{ $activeAppointment->preferred_time ?: 'N/A' }}</span>
                                    </div>
                                    <div class="admin-data-pill">
                                        <span class="label">Scheduled start</span>
                                        <span class="value">{{ $activeAppointment->scheduled_at?->format('d M Y, H:i') ?: 'Not scheduled' }}</span>
                                    </div>
                                    <div class="admin-data-pill">
                                        <span class="label">Scheduled end</span>
                                        <span class="value">{{ $activeAppointment->end_time?->format('d M Y, H:i') ?: 'N/A' }}</span>
                                    </div>
                                </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-admin-stroke bg-white/5 p-4 space-y-3">
                        <p class="text-xs uppercase tracking-[0.12em] text-admin-muted font-bold">Appointment Context</p>
                        <div class="grid gap-2 sm:grid-cols-3">
                            <div class="admin-data-pill">
                                <span class="label">Event type</span>
                                <span class="value">{{ $activeAppointment->event_type_name ?: ($activeAppointment->service?->name ?: 'General') }}</span>
                            </div>
                            <div class="admin-data-pill">
                                <span class="label">County</span>
                                <span class="value">{{ $activeAppointment->county?->name ?: 'N/A' }}</span>
                            </div>
                            <div class="admin-data-pill">
                                <span class="label">Township</span>
                                <span class="value">{{ $activeAppointment->township?->name ?: 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    @if($activeAppointment->calendly_event_id || $activeAppointment->calendly_invitee_id || $activeAppointment->reschedule_url || $activeAppointment->cancel_url)
                        <div class="rounded-xl border border-indigo-500/30 bg-indigo-500/5 p-4 space-y-3">
                            <p class="text-xs uppercase tracking-[0.12em] text-indigo-300 font-bold">Calendly Integration</p>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <div class="admin-data-pill">
                                    <span class="label">Event ID</span>
                                    <span class="value font-mono text-[11px]">{{ $activeAppointment->calendly_event_id ?: 'N/A' }}</span>
                                </div>
                                <div class="admin-data-pill">
                                    <span class="label">Invitee ID</span>
                                    <span class="value font-mono text-[11px]">{{ $activeAppointment->calendly_invitee_id ?: 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                @if($activeAppointment->reschedule_url)
                                    <a href="{{ $activeAppointment->reschedule_url }}" target="_blank" class="admin-ghost-btn h-9 gap-1 text-xs">
                                        <x-admin.icon name="refresh" class="h-3.5 w-3.5" /> Reschedule Link
                                    </a>
                                @endif
                                @if($activeAppointment->cancel_url)
                                    <a href="{{ $activeAppointment->cancel_url }}" target="_blank" class="admin-ghost-btn h-9 gap-1 text-xs">
                                        <x-admin.icon name="close" class="h-3.5 w-3.5" /> Cancel Link
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="rounded-xl border border-admin-stroke bg-white/5 p-4">
                        <p class="text-xs uppercase tracking-[0.12em] text-admin-muted font-bold">Client Message / Notes</p>
                        <p class="mt-2 whitespace-pre-line text-sm text-admin-muted">{{ $activeAppointment->message ?: 'No additional message provided.' }}</p>
                    </div>
                </div>
            @else
                <p class="text-admin-muted">This appointment is no longer available.</p>
            @endif
        </div>
    </section>

    {{-- Email Modal --}}
    @if($showEmailModal)
        <div class="admin-modal-backdrop" @click="$wire.closeEmailModal()"></div>
        <section class="admin-modal-panel max-w-2xl" role="dialog" aria-modal="true">
            <header class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-xs uppercase tracking-[0.18em] text-admin-muted">Communication</p>
                    <h3 class="text-xl font-semibold">Send Email</h3>
                </div>
                <button type="button" wire:click="closeEmailModal" class="admin-icon-btn h-10 w-10">
                    <x-admin.icon name="close" class="h-4 w-4" />
                </button>
            </header>

            <form wire:submit="sendEmail" class="space-y-4">
                <div>
                    <label class="admin-label">To</label>
                    <input type="text" class="admin-input bg-white/5" value="{{ $emailTo }}" disabled>
                </div>

                <div>
                    <label class="admin-label">Subject</label>
                    <input type="text" wire:model="emailSubject" class="admin-input" placeholder="Enter subject...">
                    @error('emailSubject') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="admin-label">Message</label>
                    <textarea wire:model="emailBody" class="admin-input min-h-[200px]" placeholder="Type your message here..."></textarea>
                    @error('emailBody') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="closeEmailModal" class="admin-ghost-btn">Cancel</button>
                    <button type="submit" class="admin-primary-btn px-6" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="sendEmail">Send Message</span>
                        <span wire:loading wire:target="sendEmail" class="inline-flex items-center gap-2">
                            <x-admin.icon name="refresh" class="h-4 w-4 animate-spin" />
                            Sending...
                        </span>
                    </button>
                </div>
            </form>
        </section>
    @endif
</div>



