<div class="space-y-5" wire:init="loadData" x-data="{ showModal: false, isLoading: false, showEmailModal: @entangle('showEmailModal'), showFileViewer: @entangle('showFileViewer') }">
    <header class="glass-card p-6 lg:p-8">
        <p class="text-xs uppercase tracking-[0.22em] text-admin-muted">Recruitment</p>
        <h2 class="mt-2 inline-flex items-center gap-2 text-3xl font-semibold">
            <x-admin.icon name="briefcase" class="h-6 w-6 text-primary-100" />
            Applications
        </h2>
        <p class="mt-2 text-admin-muted">Review incoming applications, inspect full candidate data, and communicate with applicants from one queue.</p>
    </header>

    <div class="glass-card p-4 md:p-5 space-y-4 relative z-50 overflow-visible">
        <div class="grid gap-3 lg:grid-cols-[1.5fr_1fr_auto] items-end">
            <div class="admin-filter-field">
                <label class="admin-label">Search Applications</label>
                <div class="admin-input-wrap relative">
                    <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-admin-muted" />
                    <input type="text" class="admin-input pl-9" placeholder="Search name, role type, email, phone, id..." wire:model.live.debounce.250ms="search">
                </div>
            </div>

            <div class="admin-filter-field">
                <label class="admin-label">Role Type</label>
                <x-admin.select wire:model.live="profession" placeholder="All role types">
                    <x-admin.option value="all">All role types</x-admin.option>
                    @foreach($professions as $professionValue)
                        <x-admin.option value="{{ $professionValue }}">{{ str($professionValue)->replace('-', ' ')->title() }}</x-admin.option>
                    @endforeach
                </x-admin.select>
            </div>

            @if($profession !== 'all' || trim($search) !== '' || $sortBy !== 'created_at' || $sortDirection !== 'desc')
                <button type="button" wire:click="resetFilters" class="admin-ghost-btn gap-2 whitespace-nowrap h-[42px]">
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
            <div class="admin-skeleton h-14"></div>
        </div>
    @else
        <div class="glass-card overflow-visible relative z-10">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[840px] text-sm">
                    <thead class="bg-white/5 text-admin-muted">
                        <tr>
                            <th class="text-left p-3">
                                <button type="button" wire:click="setSort('full_name')" class="admin-table-head-btn">
                                    Candidate
                                    <x-admin.icon name="sort" class="h-3.5 w-3.5" />
                                </button>
                            </th>
                            <th class="text-left p-3">
                                <button type="button" wire:click="setSort('professional_type')" class="admin-table-head-btn">
                                    Role
                                    <x-admin.icon name="sort" class="h-3.5 w-3.5" />
                                </button>
                            </th>
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
                            <th class="text-right p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($joinRequests as $request)
                            <tr class="border-t border-admin-stroke hover:bg-white/5 transition-colors duration-[140ms]">
                                <td class="p-3">
                                    <p class="font-medium">{{ $request->full_name }}</p>
                                    <p class="text-xs text-admin-muted">{{ $request->email }}</p>
                                </td>
                                <td class="p-3">
                                    <div class="flex flex-col">
                                        <span>{{ $request->professional_type ? str($request->professional_type)->replace('-', ' ')->title() : 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <span class="admin-status-badge is-{{ $request->status }}">{{ str($request->status)->replace('_', ' ')->title() }}</span>
                                </td>
                                <td class="p-3 text-admin-muted">{{ optional($request->created_at)->format('d M Y, H:i') }}</td>
                                <td class="p-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button" @click="showModal = true; isLoading = true; $wire.openPreview({{ $request->id }}).then(() => isLoading = false)" class="admin-icon-btn" title="View details">
                                            <x-admin.icon name="eye" class="h-4 w-4" />
                                        </button>

                                        <button type="button" wire:click="openEmailModal({{ $request->id }})" class="admin-icon-btn" title="Email applicant">
                                            <x-admin.icon name="mail" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-10 text-center text-admin-muted">No applications match your filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $joinRequests->links('livewire.admin-pagination') }}</div>
        </div>
    @endif

    <div x-show="showModal" class="admin-modal-backdrop" @click="showModal = false; $wire.closePreview()" x-cloak></div>
    <section x-show="showModal" class="admin-modal-panel max-h-[90vh] overflow-y-auto !max-w-4xl" role="dialog" aria-modal="true" x-cloak>
        <div x-show="isLoading" class="flex flex-col gap-3">
            <div class="admin-skeleton h-8 w-1/2"></div>
            <div class="admin-skeleton h-16"></div>
            <div class="admin-skeleton h-16"></div>
            <div class="admin-skeleton h-32"></div>
        </div>

        <div x-show="!isLoading">
            @if($activeRequest)
                <header class="flex flex-wrap items-start justify-between gap-3 border-b border-admin-stroke pb-5 mb-6">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-admin-muted">Application Review</p>
                        <h3 class="mt-1 text-2xl font-semibold">{{ $activeRequest->full_name }}</h3>
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-admin-muted">
                            <span class="rounded-full border border-admin-stroke px-2.5 py-1">ID #{{ $activeRequest->id }}</span>
                            <span class="rounded-full border border-admin-stroke px-2.5 py-1">{{ $activeRequest->professional_type ? str($activeRequest->professional_type)->replace('-', ' ')->title() : 'N/A' }}</span>
                            <span class="rounded-full border border-admin-stroke px-2.5 py-1">{{ $activeRequest->position_applied_for ?: 'No position set' }}</span>
                            <span class="admin-status-badge is-{{ $request->status }}">{{ str($activeRequest->status)->replace('_', ' ')->title() }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="isLoading = true; $wire.goToPrevious().then(() => isLoading = false)" class="admin-modal-nav-btn {{ $canMovePrevious ? '' : 'is-disabled' }}" @disabled(! $canMovePrevious) title="Previous application">
                            <x-admin.icon name="chevron-up" class="h-4 w-4" />
                            Prev
                        </button>
                        <button type="button" @click="isLoading = true; $wire.goToNext().then(() => isLoading = false)" class="admin-modal-nav-btn {{ $canMoveNext ? '' : 'is-disabled' }}" @disabled(! $canMoveNext) title="Next application">
                            <x-admin.icon name="chevron-down" class="h-4 w-4" />
                            Next
                        </button>
                        <button type="button" @click="showModal = false; $wire.closePreview()" class="admin-icon-btn h-10 w-10" title="Close">
                            <x-admin.icon name="close" class="h-4 w-4" />
                        </button>
                    </div>
                </header>

                <div class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-admin-stroke bg-white/5 p-4 space-y-3">
                            <p class="text-xs uppercase tracking-[0.12em] text-admin-muted font-bold">Contact Details</p>
                            <div class="space-y-2 overflow-hidden">
                                <p class="flex items-center gap-2 text-sm text-admin-ink truncate"><x-admin.icon name="mail" class="h-4 w-4 text-admin-muted shrink-0" /> {{ $activeRequest->email }}</p>
                                <p class="flex items-center gap-2 text-sm text-admin-ink truncate"><x-admin.icon name="phone" class="h-4 w-4 text-admin-muted shrink-0" /> {{ $activeRequest->phone ?: 'Not provided' }}</p>
                                <p class="flex items-start gap-2 text-sm text-admin-ink"><x-admin.icon name="location" class="h-4 w-4 text-admin-muted shrink-0 mt-0.5" /> <span class="break-words">{{ $activeRequest->home_address ?: 'No address' }}</span></p>
                            </div>
                        </div>
                        <div class="rounded-xl border border-admin-stroke bg-white/5 p-4 space-y-3">
                            <p class="text-xs uppercase tracking-[0.12em] text-admin-muted font-bold">Application Snapshot</p>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="admin-data-pill !p-2">
                                    <span class="label !text-[10px]">Submitted</span>
                                    <span class="value !text-[11px]">{{ optional($activeRequest->created_at)->format('d M Y') }}</span>
                                </div>
                                <div class="admin-data-pill !p-2">
                                    <span class="label !text-[10px]">Available</span>
                                    <span class="value !text-[11px]">{{ $activeRequest->date_available ? $activeRequest->date_available->format('d M Y') : 'N/A' }}</span>
                                </div>
                                <div class="admin-data-pill !p-2">
                                    <span class="label !text-[10px]">Salary</span>
                                    <span class="value !text-[11px] truncate">{{ $activeRequest->desired_salary ?: 'N/A' }}</span>
                                </div>
                                <div class="admin-data-pill !p-2">
                                    <span class="label !text-[10px]">Prof. Type</span>
                                    <span class="value !text-[11px] truncate">{{ $activeRequest->professional_type ?: 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-admin-stroke bg-white/5 p-4 space-y-4">
                        <p class="text-xs uppercase tracking-[0.12em] text-admin-muted font-bold">Eligibility & Compliance</p>
                        <div class="grid gap-2 grid-cols-2 sm:grid-cols-4 lg:grid-cols-6">
                            <div class="admin-data-pill !p-2">
                                <span class="label !text-[10px]">Citizen</span>
                                <span class="value !text-[11px]">{{ $activeRequest->is_citizen ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="admin-data-pill !p-2">
                                <span class="label !text-[10px]">Authorized</span>
                                <span class="value !text-[11px]">{{ $activeRequest->is_authorized ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="admin-data-pill !p-2">
                                <span class="label !text-[10px]">Worked here</span>
                                <span class="value !text-[11px]">{{ $activeRequest->worked_here_before ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="admin-data-pill !p-2">
                                <span class="label !text-[10px]">Felony</span>
                                <span class="value !text-[11px] {{ $activeRequest->has_felony ? 'text-red-400' : '' }}">{{ $activeRequest->has_felony ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="admin-data-pill !p-2">
                                <span class="label !text-[10px]">Signed</span>
                                <span class="value !text-[11px]">{{ $activeRequest->disclaimer_accepted ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="admin-data-pill !p-2">
                                <span class="label !text-[10px]">DOB</span>
                                <span class="value !text-[11px]">{{ $activeRequest->dob ? $activeRequest->dob->format('d M Y') : 'N/A' }}</span>
                            </div>
                        </div>
                        @if($activeRequest->has_felony && $activeRequest->felony_explanation)
                            <div class="rounded-lg border border-admin-stroke p-3 bg-white/5">
                                <p class="text-[10px] font-bold text-admin-muted uppercase mb-1">Felony explanation</p>
                                <p class="text-sm text-admin-ink whitespace-pre-line leading-relaxed">{{ $activeRequest->felony_explanation }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @if(! empty($activeRequest->references))
                            <div class="rounded-xl border border-admin-stroke bg-white/5 p-4 space-y-3">
                                <p class="text-xs uppercase tracking-[0.12em] text-admin-muted font-bold">References</p>
                                <div class="space-y-2">
                                    @foreach($activeRequest->references as $ref)
                                        <div class="text-[11px] p-2.5 rounded-lg bg-white/5 border border-admin-stroke/50">
                                            <p class="font-bold text-admin-ink">{{ $ref['name'] ?? 'N/A' }}</p>
                                            <p class="text-admin-muted mt-0.5">{{ $ref['relationship'] ?? 'Professional' }} • {{ $ref['phone'] ?? 'N/A' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="rounded-xl border border-admin-stroke bg-white/5 p-4 space-y-3">
                            <p class="text-xs uppercase tracking-[0.12em] text-admin-muted font-bold">Documents & Notes</p>
                            <div class="space-y-3">
                                @php
                                    $docs = $activeRequest->document_paths ?? ($activeRequest->resume_path ? [$activeRequest->resume_path] : []);
                                @endphp

                                <div class="grid gap-2">
                                    @forelse($docs as $idx => $path)
                                        <button 
                                            type="button" 
                                            wire:click="openFileViewer('{{ $path }}', {{ $idx }})" 
                                            class="flex items-center gap-2 p-2.5 rounded-lg border transition-colors text-xs font-medium {{ $showFileViewer && $activeFileIndex === $idx ? 'border-primary-500 bg-primary-500/10 text-primary-100' : 'border-admin-stroke bg-white/5 text-admin-muted hover:border-admin-muted' }}"
                                        >
                                            <x-admin.icon name="article" class="h-4 w-4 shrink-0" />
                                            <span class="truncate">{{ $idx === 0 ? 'Primary Resume' : 'Doc ' . ($idx + 1) }}</span>
                                        </button>
                                    @empty
                                        <div class="p-3 rounded border border-dashed border-admin-stroke text-center text-xs text-admin-muted italic">No documents</div>
                                    @endforelse

                                    @if($activeRequest->signature_path)
                                        <button 
                                            type="button" 
                                            wire:click="openFileViewer('{{ $activeRequest->signature_path }}', 99)" 
                                            class="flex items-center gap-2 p-2.5 rounded-lg border transition-colors text-xs font-medium {{ $showFileViewer && $activeFileIndex === 99 ? 'border-primary-500 bg-primary-500/10 text-primary-100' : 'border-admin-stroke bg-white/5 text-admin-muted hover:border-admin-muted' }}"
                                        >
                                            <x-admin.icon name="check" class="h-4 w-4 shrink-0" />
                                            <span>View Signature</span>
                                        </button>
                                    @endif
                                </div>

                                @if($activeRequest->about)
                                    <div class="p-3 rounded-lg bg-white/5 border border-admin-stroke mt-2">
                                        <p class="text-[10px] font-bold text-admin-muted uppercase mb-1">Applicant summary</p>
                                        <p class="text-[11px] text-admin-muted italic leading-relaxed">"{{ $activeRequest->about }}"</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Dedicated Preview Card --}}
                    @if($showFileViewer)
                        <div class="rounded-xl border border-admin-stroke bg-black/40 overflow-hidden flex flex-col h-[600px] relative shadow-2xl">
                            <header class="bg-white/5 p-4 flex items-center justify-between border-b border-admin-stroke">
                                <span class="text-xs font-bold uppercase tracking-widest text-admin-muted inline-flex items-center gap-2">
                                    <x-admin.icon name="article" class="h-4 w-4 text-primary-100" />
                                    File Preview: {{ $activeFileIndex === 99 ? 'Signature' : ($activeFileIndex === 0 ? 'Resume' : 'Doc ' . ($activeFileIndex + 1)) }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <a href="{{ Storage::url($activeFilePath) }}" target="_blank" class="admin-icon-btn h-8 w-8" title="Download">
                                        <x-admin.icon name="refresh" class="h-4 w-4" />
                                    </a>
                                    <button type="button" wire:click="closeFileViewer" class="admin-icon-btn h-8 w-8">
                                        <x-admin.icon name="close" class="h-4 w-4" />
                                    </button>
                                </div>
                            </header>
                            <div class="flex-1 bg-admin-bg relative overflow-hidden flex items-center justify-center p-4">
                                @if($activeFileType === 'pdf')
                                    <iframe src="{{ Storage::url($activeFilePath) }}#toolbar=0" class="w-full h-full border-none rounded-lg bg-white"></iframe>
                                @elseif($activeFileType === 'image')
                                    <img src="{{ Storage::url($activeFilePath) }}" class="max-w-full max-h-full object-contain shadow-lg rounded-lg" alt="Document preview">
                                @else
                                    <div class="text-center p-8">
                                        <x-admin.icon name="info" class="h-10 w-10 text-admin-muted mx-auto mb-3" />
                                        <p class="text-sm text-admin-muted">Preview unavailable for this format.</p>
                                        <a href="{{ Storage::url($activeFilePath) }}" target="_blank" class="admin-link text-xs mt-2 inline-block">Download instead</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <footer class="mt-8 pt-6 border-t border-admin-stroke flex flex-wrap items-center justify-end gap-3">
                    <button type="button" wire:click="openEmailModal({{ $activeRequest->id }})" class="admin-primary-btn gap-2">
                        <x-admin.icon name="mail" class="h-4 w-4" />
                        Send Correspondence
                    </button>
                    <button type="button" @click="showModal = false; $wire.closePreview()" class="admin-ghost-btn">
                        Done Reviewing
                    </button>
                </footer>
            @else
                <p class="text-admin-muted text-center py-20">This application is no longer available.</p>
            @endif
        </div>
    </section>

    {{-- Email Modal --}}
    <div x-show="showEmailModal" class="admin-modal-backdrop !z-[100]" @click="showEmailModal = false; $wire.closeEmailModal()" x-cloak></div>
    <section x-show="showEmailModal" class="admin-modal-panel !z-[101] !max-w-2xl" x-cloak>
        <header class="flex items-center justify-between border-b border-admin-stroke pb-4 mb-6">
            <div>
                <h3 class="text-xl font-semibold">Send Correspondence</h3>
                <p class="text-xs text-admin-muted mt-1">Compose an email to the applicant</p>
            </div>
            <button type="button" @click="showEmailModal = false; $wire.closeEmailModal()" class="admin-icon-btn">
                <x-admin.icon name="close" class="h-4 w-4" />
            </button>
        </header>

        <form wire:submit.prevent="sendEmail" class="space-y-4">
            <div class="admin-filter-field">
                <label class="admin-label">Recipient</label>
                <input type="text" class="admin-input bg-white/5" wire:model="emailTo" readonly disabled>
            </div>

            <div class="admin-filter-field">
                <label class="admin-label">Subject Line</label>
                <input type="text" class="admin-input @error('emailSubject') border-red-500 @enderror" wire:model="emailSubject" placeholder="Enter subject...">
                @error('emailSubject') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="admin-filter-field">
                <label class="admin-label">Message Body</label>
                <textarea rows="10" class="admin-input resize-none @error('emailBody') border-red-500 @enderror" wire:model="emailBody" placeholder="Type your message here..."></textarea>
                @error('emailBody') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-admin-stroke">
                <button type="button" @click="showEmailModal = false; $wire.closeEmailModal()" class="admin-ghost-btn">Cancel</button>
                <button type="submit" class="admin-primary-btn gap-2" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="sendEmail">
                        <x-admin.icon name="mail" class="h-4 w-4" />
                        Send Email
                    </span>
                    <span wire:loading wire:target="sendEmail" class="flex items-center gap-2">
                        <span class="tiny-orb-loader"></span> Sending...
                    </span>
                </button>
            </div>
        </form>
    </section>
</div>
