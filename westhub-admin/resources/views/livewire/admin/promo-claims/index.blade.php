<div class="space-y-4">
    <div class="glass-card p-5 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-2xl font-semibold">Promo Claims</h2>
            <p class="text-admin-muted mt-1">
                {{ $offer->offerAmount }} {{ $offer->offerHighlight }} campaign.
                Vouchers are emailed automatically, so there is usually nothing to do here.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="admin-chip">
                {{ $offer->enabled ? 'Popup is live' : 'Popup is paused' }}
            </span>
            <span class="admin-chip">{{ $sheetsEnabled ? 'Syncing to Sheets' : 'Sheets off' }}</span>
            @can('promos.export')
                <button wire:click="export" class="admin-ghost-btn">Export CSV</button>
            @endcan
            @can('settings.manage')
                <a href="{{ route('admin.settings.index') }}" class="admin-primary-btn">Campaign settings</a>
            @endcan
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="glass-card p-4">
            <p class="admin-label">Total claims</p>
            <p class="text-2xl font-semibold">{{ $totalClaims }}</p>
        </div>
        <div class="glass-card p-4">
            <p class="admin-label">Redeemed</p>
            <p class="text-2xl font-semibold">{{ $redeemedCount }}</p>
        </div>
        <div class="glass-card p-4">
            <p class="admin-label">Conversion to booking</p>
            <p class="text-2xl font-semibold">
                {{ $totalClaims > 0 ? round(($redeemedCount / $totalClaims) * 100) : 0 }}%
            </p>
        </div>
    </div>

    <div class="glass-card p-4 space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                class="admin-input max-w-xs"
                placeholder="Name, email, phone or voucher"
            >

            <button wire:click="setStatus('all')" class="admin-chip {{ $status === 'all' ? 'is-active' : '' }}">
                All
            </button>
            @foreach($statuses as $statusOption)
                <button
                    wire:key="promo-status-{{ $statusOption }}"
                    wire:click="setStatus('{{ $statusOption }}')"
                    class="admin-chip {{ $status === $statusOption ? 'is-active' : '' }}"
                >
                    {{ str($statusOption)->headline() }}
                    <span class="text-admin-muted">{{ $counts[$statusOption] ?? 0 }}</span>
                </button>
            @endforeach
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-admin-muted">
                        <th class="py-2 pr-4 font-medium">Claimed</th>
                        <th class="py-2 pr-4 font-medium">Person</th>
                        <th class="py-2 pr-4 font-medium">Service</th>
                        <th class="py-2 pr-4 font-medium">Voucher</th>
                        <th class="py-2 pr-4 font-medium">Status</th>
                        <th class="py-2 pr-4 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($claims as $claim)
                        <tr class="border-t border-admin-stroke align-top" wire:key="promo-claim-{{ $claim->id }}">
                            <td class="py-3 pr-4 whitespace-nowrap">
                                <div>{{ $claim->created_at?->format('j M Y') }}</div>
                                <div class="text-xs text-admin-muted">{{ $claim->created_at?->diffForHumans() }}</div>
                            </td>
                            <td class="py-3 pr-4">
                                <div class="font-medium">{{ $claim->full_name }}</div>
                                <div class="text-xs text-admin-muted">
                                    <a href="mailto:{{ $claim->email }}" class="underline">{{ $claim->email }}</a>
                                    @if($claim->phone) · {{ $claim->phone }} @endif
                                </div>
                            </td>
                            <td class="py-3 pr-4">{{ $claim->service?->name ?? '—' }}</td>
                            <td class="py-3 pr-4 whitespace-nowrap">
                                <div class="font-mono text-xs">{{ $claim->voucher_code }}</div>
                                @if($claim->expires_at)
                                    <div class="text-xs text-admin-muted">
                                        {{ $claim->expires_at->isPast() ? 'Expired' : 'Expires' }} {{ $claim->expires_at->format('j M') }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 pr-4">
                                <span class="admin-chip">{{ str($claim->status)->headline() }}</span>
                                @if($claim->appointment_id)
                                    <div class="mt-1 text-xs text-admin-muted">Appointment #{{ $claim->appointment_id }}</div>
                                @endif
                            </td>
                            <td class="py-3 pr-4">
                                @can('promos.manage')
                                    <div class="flex flex-wrap gap-1.5">
                                        @if($claim->status === 'new')
                                            <button wire:click="markContacted({{ $claim->id }})" class="admin-ghost-btn !px-2 !py-1 text-xs">Contacted</button>
                                        @endif
                                        @if($claim->status !== 'redeemed' && $claim->status !== 'cancelled')
                                            <button wire:click="markRedeemed({{ $claim->id }})" class="admin-ghost-btn !px-2 !py-1 text-xs">Redeemed</button>
                                        @endif
                                        <button wire:click="resendVoucher({{ $claim->id }})" class="admin-ghost-btn !px-2 !py-1 text-xs">Resend</button>
                                        @if($claim->status !== 'cancelled')
                                            <button
                                                wire:click="cancelClaim({{ $claim->id }})"
                                                wire:confirm="Cancel this voucher? The client will no longer be able to redeem it."
                                                class="admin-ghost-btn !px-2 !py-1 text-xs"
                                            >Cancel</button>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-admin-muted">View only</span>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="admin-empty-state p-8 text-center">
                                    <p class="text-admin-muted">
                                        No claims yet.
                                        @if(! $offer->enabled) The popup is currently switched off in Settings. @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $claims->links() }}
    </div>
</div>
