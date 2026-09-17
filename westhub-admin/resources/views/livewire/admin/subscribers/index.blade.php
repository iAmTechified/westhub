<div class="space-y-5" x-data="{ showNewsletterModal: $wire.entangle('showNewsletterModal') }">
    <header class="glass-card p-6 lg:p-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.22em] text-admin-muted">Marketing</p>
                <h2 class="mt-2 inline-flex items-center gap-2 text-3xl font-semibold">
                    <x-admin.icon name="mail" class="h-6 w-6 text-primary-100" />
                    Newsletter Subscribers
                </h2>
                <p class="mt-2 text-admin-muted">Manage your mailing list, export subscriber data, and send out email broadcasts.</p>
            </div>
            <div class="flex items-center gap-3">
                @can('subscribers.export')
                <button type="button" wire:click="exportCsv" class="admin-ghost-btn gap-2 h-[46px] px-5">
                    <x-admin.icon name="refresh" class="h-4 w-4" />
                    Export CSV
                </button>
                @endcan
                @can('subscribers.send')
                <button type="button" @click="showNewsletterModal = true" wire:click="openNewsletterModal" class="admin-primary-btn gap-2 h-[46px] px-6">
                    <x-admin.icon name="mail" class="h-4 w-4" />
                    Send Newsletter
                </button>
                @endcan
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-admin-stroke bg-white/5 p-4">
                <p class="text-xs text-admin-muted uppercase tracking-wider font-bold">Total Subscribers</p>
                <p class="text-2xl font-semibold mt-1">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="rounded-2xl border border-admin-stroke bg-white/5 p-4">
                <p class="text-xs text-primary-100/60 uppercase tracking-wider font-bold">Active</p>
                <p class="text-2xl font-semibold mt-1 text-primary-100">{{ number_format($stats['active']) }}</p>
            </div>
            <div class="rounded-2xl border border-admin-stroke bg-white/5 p-4">
                <p class="text-xs text-red-400/60 uppercase tracking-wider font-bold">Unsubscribed</p>
                <p class="text-2xl font-semibold mt-1 text-red-400">{{ number_format($stats['unsubscribed']) }}</p>
            </div>
        </div>
    </header>

    <div class="glass-card p-4 md:p-5 space-y-4 relative z-50 overflow-visible">
        <div class="grid gap-3 lg:grid-cols-[2fr_1fr_auto] items-end">
            <div class="admin-filter-field">
                <label class="admin-label">Search Subscribers</label>
                <div class="admin-input-wrap relative">
                    <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-admin-muted" />
                    <input type="text" class="admin-input pl-9" placeholder="Search email, name, source..." wire:model.live.debounce.250ms="search">
                </div>
            </div>

            <div class="admin-filter-field">
                <label class="admin-label">Status</label>
                <x-admin.select wire:model.live="status">
                    <x-admin.option value="all">All Statuses</x-admin.option>
                    <x-admin.option value="subscribed">Subscribed</x-admin.option>
                    <x-admin.option value="unsubscribed">Unsubscribed</x-admin.option>
                </x-admin.select>
            </div>

            @if($status !== 'all' || trim($search) !== '' || $sortBy !== 'created_at' || $sortDirection !== 'desc')
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
        </div>
    @else
        <div class="glass-card overflow-visible relative z-10">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[840px] text-sm">
                    <thead class="bg-white/5 text-admin-muted">
                        <tr>
                            <th class="text-left p-4">
                                <button type="button" wire:click="setSort('email')" class="admin-table-head-btn">
                                    Subscriber
                                    <x-admin.icon name="sort" class="h-3.5 w-3.5" />
                                </button>
                            </th>
                            <th class="text-left p-4">
                                <button type="button" wire:click="setSort('source')" class="admin-table-head-btn">
                                    Source
                                    <x-admin.icon name="sort" class="h-3.5 w-3.5" />
                                </button>
                            </th>
                            <th class="text-left p-4">
                                <button type="button" wire:click="setSort('status')" class="admin-table-head-btn">
                                    Status
                                    <x-admin.icon name="sort" class="h-3.5 w-3.5" />
                                </button>
                            </th>
                            <th class="text-left p-4">
                                <button type="button" wire:click="setSort('subscribed_at')" class="admin-table-head-btn">
                                    Date Subscribed
                                    <x-admin.icon name="sort" class="h-3.5 w-3.5" />
                                </button>
                            </th>
                            <th class="text-right p-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscribers as $subscriber)
                            <tr class="border-t border-admin-stroke hover:bg-white/5 transition-colors duration-[140ms]">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-full bg-primary-500/10 flex items-center justify-center text-primary-100 font-bold">
                                            {{ strtoupper(substr($subscriber->email, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-admin-ink">{{ $subscriber->email }}</p>
                                            <p class="text-xs text-admin-muted">{{ $subscriber->full_name ?: 'No name provided' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-admin-stroke text-admin-muted capitalize">
                                        {{ $subscriber->source }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="admin-status-badge is-{{ $subscriber->status }}">
                                        {{ ucfirst($subscriber->status) }}
                                    </span>
                                </td>
                                <td class="p-4 text-admin-muted">
                                    {{ $subscriber->subscribed_at?->format('d M Y, H:i') ?: 'N/A' }}
                                </td>
                                <td class="p-4 text-right">
                                    @can('subscribers.manage')
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button"
                                                wire:click="toggleStatus({{ $subscriber->id }})"
                                                class="admin-icon-btn h-9 w-9" 
                                                title="{{ $subscriber->status === 'subscribed' ? 'Unsubscribe' : 'Resubscribe' }}">
                                            <x-admin.icon name="{{ $subscriber->status === 'subscribed' ? 'close' : 'check' }}" class="h-4 w-4" />
                                        </button>
                                        <button type="button" 
                                                wire:confirm="Are you sure you want to delete this subscriber? This action cannot be undone."
                                                wire:click="deleteSubscriber({{ $subscriber->id }})" 
                                                class="admin-icon-btn h-9 w-9 text-red-400 hover:text-red-500 hover:bg-red-500/10" 
                                                title="Delete">
                                            <x-admin.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-16 text-center">
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <div class="h-12 w-12 rounded-full bg-admin-stroke flex items-center justify-center text-admin-muted">
                                            <x-admin.icon name="mail" class="h-6 w-6" />
                                        </div>
                                        <p class="text-admin-muted font-medium">No subscribers found.</p>
                                        @if($status !== 'all' || trim($search) !== '')
                                            <button type="button" wire:click="resetFilters" class="admin-link text-sm">Clear all filters</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-admin-stroke">
                {{ $subscribers->links('livewire.admin-pagination') }}
            </div>
        </div>
    @endif

    {{-- Newsletter Modal --}}
    <div x-data="{ open: $wire.entangle('showNewsletterModal') }" x-show="open" x-cloak class="relative z-[8000]">
        <div class="admin-modal-backdrop" @click="open = false; $wire.closeNewsletterModal()"></div>
        <section class="admin-modal-panel !max-w-[min(768px,94vw)]">
            <header class="flex items-center justify-between border-b border-admin-stroke pb-4 mb-6">
                <div>
                    <h3 class="text-xl font-semibold">Send Email Broadcast</h3>
                    <p class="text-xs text-admin-muted mt-1">This will be sent to all <strong>{{ number_format($stats['active']) }}</strong> active subscribers.</p>
                </div>
                <button type="button" @click="open = false; $wire.closeNewsletterModal()" class="admin-icon-btn">
                    <x-admin.icon name="close" class="h-4 w-4" />
                </button>
            </header>

            <form class="space-y-5">
                <div class="admin-filter-field">
                    <label class="admin-label">Subject Line</label>
                    <div class="admin-input-wrap">
                        <input type="text" class="admin-input @error('newsletterSubject') is-invalid @enderror" wire:model="newsletterSubject" placeholder="e.g. Exciting News from WestHub Healthcare">
                    </div>
                    @error('newsletterSubject') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="admin-filter-field">
                    <label class="admin-label">Message Content</label>
                    <div class="admin-input-wrap">
                        <textarea rows="12" class="admin-input resize-none @error('newsletterBody') is-invalid @enderror" wire:model="newsletterBody" placeholder="Type your newsletter content here..."></textarea>
                    </div>
                    <p class="text-[10px] text-admin-muted mt-2">Tip: Use double line breaks to start new paragraphs. The email will use our premium WestHub template.</p>
                    @error('newsletterBody') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-admin-stroke">
                    <button type="button" @click="open = false; $wire.closeNewsletterModal()" class="admin-ghost-btn">Cancel</button>
                    <button type="button" wire:click="sendNewsletter" class="admin-primary-btn gap-2 px-8" wire:loading.attr="disabled" wire:target="sendNewsletter">
                        <span wire:loading.remove wire:target="sendNewsletter">
                            <x-admin.icon name="send" class="h-4 w-4" />
                            Broadcast Newsletter
                        </span>
                        <span wire:loading wire:target="sendNewsletter" class="flex items-center gap-2">
                            <span class="tiny-orb-loader"></span> Queuing...
                        </span>
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
