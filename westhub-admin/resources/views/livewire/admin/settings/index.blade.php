<div class="space-y-4" wire:init="loadData">
    <div class="glass-card p-5">
        <h2 class="inline-flex items-center gap-2 text-2xl font-semibold">
            <x-admin.icon name="settings" class="h-5 w-5 text-primary-100" />
            Platform Settings
        </h2>
        <p class="text-admin-muted mt-1">Brand, SEO, email, integrations, content defaults, and security controls.</p>
    </div>

    @if(! $readyToLoad)
        <div class="grid gap-4 xl:grid-cols-[240px_1fr]">
            <aside class="glass-card p-4 space-y-2">
                <div class="admin-skeleton h-10"></div>
                <div class="admin-skeleton h-10"></div>
                <div class="admin-skeleton h-10"></div>
            </aside>
            <section class="glass-card p-4 space-y-3">
                <div class="admin-skeleton h-16"></div>
                <div class="admin-skeleton h-16"></div>
                <div class="admin-skeleton h-10 w-40"></div>
            </section>
        </div>
    @else
        <div class="flex flex-col xl:grid gap-4 xl:grid-cols-[240px_1fr]">
            <aside class="glass-card p-2 flex overflow-x-auto gap-2 xl:flex-col xl:p-4 xl:space-y-2 no-scrollbar scroll-smooth">
                @foreach($groups as $groupName)
                    @php $groupMeta = \App\Livewire\Admin\Settings\Index::GROUP_DEFINITIONS[$groupName]['_meta'] ?? []; @endphp
                    <button wire:click="setGroup('{{ $groupName }}')" class="admin-nav-link shrink-0 xl:w-full {{ $group === $groupName ? 'is-active' : '' }}">
                        <x-admin.icon name="{{ $groupMeta['icon'] ?? 'settings' }}" class="h-4 w-4" />
                        <span class="whitespace-nowrap">{{ str($groupName)->title() }}</span>
                    </button>
                @endforeach
            </aside>

            <section class="glass-card p-5 space-y-6">
                @php $currentMeta = \App\Livewire\Admin\Settings\Index::GROUP_DEFINITIONS[$group]['_meta'] ?? []; @endphp
                <div class="pb-4 border-b border-admin-stroke">
                    <div class="flex items-center gap-3">
                        <div class="grid place-items-center h-10 w-10 rounded-xl bg-primary-100/10 text-primary-100 shadow-sm border border-primary-100/20">
                            <x-admin.icon name="{{ $currentMeta['icon'] ?? 'settings' }}" class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-admin-ink leading-tight">{{ str($group)->title() }}</h3>
                            <p class="text-sm text-admin-muted mt-0.5">{{ $currentMeta['description'] ?? 'Configure settings for this group.' }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 grid-cols-1 sm:grid-cols-2">
                    @foreach($currentFields as $fieldKey => $meta)
                        @continue($fieldKey === '_meta')
                        <div class="{{ ($meta['type'] ?? 'text') === 'textarea' ? 'md:col-span-2' : '' }}">
                            <label class="admin-label flex items-center gap-1.5">
                                {{ $meta['label'] }}
                                @if($meta['required'] ?? false)
                                    <span class="text-rose-400 font-bold">*</span>
                                @endif
                            </label>
                            @if(($meta['type'] ?? 'text') === 'textarea')
                                <textarea
                                    wire:model.defer="settings.{{ $fieldKey }}"
                                    class="admin-input min-h-28"
                                    placeholder="{{ $meta['placeholder'] ?? 'Value' }}"
                                ></textarea>
                            @else
                                <input
                                    type="{{ $meta['type'] ?? 'text' }}"
                                    wire:model.defer="settings.{{ $fieldKey }}"
                                    class="admin-input"
                                    placeholder="{{ $meta['placeholder'] ?? 'Value' }}"
                                    @if(($meta['type'] ?? 'text') === 'password') autocomplete="new-password" @endif
                                >
                            @endif
                            @error('settings.'.$fieldKey)
                                <p class="text-sm text-rose-300 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="pt-2">
                    <button wire:click="updateGroup" wire:loading.attr="disabled" wire:target="updateGroup" class="admin-primary-btn min-w-32">
                        <span wire:loading.remove wire:target="updateGroup">Save Changes</span>
                        <span wire:loading wire:target="updateGroup" class="flex items-center gap-2">
                            <x-admin.icon name="spinner" class="h-4 w-4 animate-spin" />
                            Saving...
                        </span>
                    </button>
                </div>

                <div class="pt-6 mt-4 border-t border-admin-stroke">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold uppercase tracking-wider text-admin-muted">Recent Activity</h4>
                        <span class="text-[10px] text-admin-muted/60 uppercase tracking-widest">Global Audit Log</span>
                    </div>
                    
                    <div class="space-y-2 max-h-72 overflow-auto pr-1 admin-scrollbar">
                        @forelse($recentAudits as $audit)
                            <div class="admin-row-item group/audit hover:border-primary-100/30 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-admin-surface border border-admin-stroke grid place-items-center text-admin-muted group-hover/audit:text-primary-100 transition-colors">
                                        <x-admin.icon name="settings" class="h-3.5 w-3.5" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium">{{ $audit->setting?->group }}.{{ $audit->setting?->key }}</span>
                                        <span class="text-[11px] text-admin-muted">{{ $audit->actor?->name ?? 'System' }}</span>
                                    </div>
                                </div>
                                <span class="text-[11px] text-admin-muted/70">{{ $audit->changed_at?->diffForHumans() }}</span>
                            </div>
                        @empty
                            <div class="admin-empty-state py-8">
                                <p class="text-admin-muted italic">No activity logged yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    @endif
</div>
