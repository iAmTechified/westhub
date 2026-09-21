<div class="space-y-4" wire:init="loadData">
    <div class="glass-card p-5">
        <h2 class="inline-flex items-center gap-2 text-2xl font-semibold">
            <x-admin.icon name="settings" class="h-5 w-5 text-primary-100" />
            Platform Settings
        </h2>
        <p class="text-admin-muted mt-1">Email, promotions, booking provider, integrations and contact details. Changes go live on the website immediately, with no deploy.</p>
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
                    <button wire:key="settings-group-{{ $groupName }}" wire:click="setGroup('{{ $groupName }}')" class="admin-nav-link shrink-0 xl:w-full {{ $group === $groupName ? 'is-active' : '' }}">
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

                @if($hasEncryptedFields && ! $sharedKeyConfigured)
                    <div class="admin-request-feedback is-warning">
                        <strong>Encrypted keys are not shared with the website yet.</strong>
                        Run <code>php artisan westhub:settings-key</code> and put the same <code>SETTINGS_ENCRYPTION_KEY</code>
                        in the <code>.env</code> of both the admin and the public site. Until then, keys saved here cannot be read by the website.
                    </div>
                @endif

                @if(! $canManageGroup && $group !== 'password')
                    <div class="admin-request-feedback">
                        You can view these settings but not change them.
                    </div>
                @endif

                @if($testResult)
                    <div class="admin-request-feedback {{ $testResult['ok'] ? 'is-success' : 'is-warning' }}">
                        <strong>{{ $testResult['ok'] ? 'Connected.' : 'Not connected.' }}</strong>
                        {{ $testResult['message'] }}
                        {{-- Raw cURL/Google text is for developers only. --}}
                        @if(config('app.debug') && filled($testResult['detail'] ?? null))
                            <span class="mt-1 block font-mono text-[11px] opacity-80">Debug: {{ $testResult['detail'] }}</span>
                        @endif
                    </div>
                @endif

                @if($group === 'password')
                    {{-- Personal password change, available to every admin. --}}
                    <div class="grid gap-5 grid-cols-1 sm:grid-cols-2">
                        @foreach($currentFields as $fieldKey => $meta)
                            @continue($fieldKey === '_meta')
                            <div wire:key="settings-field-password-{{ $fieldKey }}">
                                <label class="admin-label flex items-center gap-1.5" for="setting-{{ $fieldKey }}">
                                    {{ $meta['label'] }}
                                    @if($meta['required'] ?? false)
                                        <span class="text-rose-400 font-bold">*</span>
                                    @endif
                                </label>
                                <input id="setting-{{ $fieldKey }}" type="password" wire:model.defer="settings.{{ $fieldKey }}" class="admin-input" autocomplete="new-password">
                                @error('settings.'.$fieldKey)
                                    <p class="text-sm text-rose-300 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                @else
                    @foreach($sections as $sectionName => $fields)
                        <div class="space-y-4" wire:key="settings-section-{{ $group }}-{{ $loop->index }}">
                            @if($sectionName !== '')
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-admin-muted">{{ $sectionName }}</h4>
                            @endif

                            <div class="grid gap-5 grid-cols-1 sm:grid-cols-2">
                                @foreach($fields as $fieldKey => $meta)
                                    @php($type = $meta['type'] ?? 'text')
                                    <div wire:key="settings-field-{{ $group }}-{{ $fieldKey }}" class="{{ in_array($type, ['textarea', 'boolean'], true) ? 'sm:col-span-2' : '' }}">
                                        @if($type === 'boolean')
                                            <label class="flex items-start gap-3 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    wire:model.live="settings.{{ $fieldKey }}"
                                                    class="mt-0.5 h-5 w-5 rounded border-admin-stroke"
                                                    @disabled(! $canManageGroup)
                                                >
                                                <span>
                                                    <span class="admin-label !mb-0">{{ $meta['label'] }}</span>
                                                    @isset($meta['help'])
                                                        <span class="block text-xs text-admin-muted mt-0.5">{{ $meta['help'] }}</span>
                                                    @endisset
                                                </span>
                                            </label>
                                        @else
                                            <label class="admin-label flex items-center gap-1.5" for="setting-{{ $fieldKey }}">
                                                {{ $meta['label'] }}
                                                @if($meta['required'] ?? false)
                                                    <span class="text-rose-400 font-bold">*</span>
                                                @endif
                                            </label>

                                            @if($type === 'textarea')
                                                <textarea
                                                    id="setting-{{ $fieldKey }}"
                                                    wire:model.defer="settings.{{ $fieldKey }}"
                                                    class="admin-input min-h-28 {{ ($meta['encrypted'] ?? false) ? 'font-mono text-xs' : '' }}"
                                                    placeholder="{{ ($meta['encrypted'] ?? false) ? 'Saved securely. Paste a new key to replace it, or leave blank to keep the current one.' : ($meta['placeholder'] ?? '') }}"
                                                    @disabled(! $canManageGroup)
                                                ></textarea>
                                            @elseif($type === 'select')
                                                <select
                                                    id="setting-{{ $fieldKey }}"
                                                    wire:model.live="settings.{{ $fieldKey }}"
                                                    class="admin-input"
                                                    @disabled(! $canManageGroup)
                                                >
                                                    @foreach($meta['options'] ?? [] as $optionValue => $optionLabel)
                                                        <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input
                                                    id="setting-{{ $fieldKey }}"
                                                    type="{{ in_array($type, ['email', 'url', 'password', 'number', 'date', 'time'], true) ? $type : 'text' }}"
                                                    wire:model.defer="settings.{{ $fieldKey }}"
                                                    class="admin-input"
                                                    placeholder="{{ $meta['placeholder'] ?? '' }}"
                                                    @if($type === 'password') autocomplete="new-password" @endif
                                                    @disabled(! $canManageGroup)
                                                >
                                            @endif

                                            @isset($meta['help'])
                                                <p class="text-xs text-admin-muted mt-1">{{ $meta['help'] }}</p>
                                            @endisset
                                            @isset($envFallbacks[$fieldKey])
                                                <p class="text-xs text-primary-100 mt-1 [overflow-wrap:anywhere]">
                                                    @if($envFallbacks[$fieldKey]['value'] === null)
                                                        Using the value from <code>{{ $envFallbacks[$fieldKey]['var'] }}</code> in <code>.env</code> until one is saved here.
                                                    @else
                                                        Using <span class="font-mono">{{ $envFallbacks[$fieldKey]['value'] }}</span> from <code>{{ $envFallbacks[$fieldKey]['var'] }}</code> in <code>.env</code> until a value is saved here.
                                                    @endif
                                                </p>
                                            @endisset
                                        @endif

                                        @error('settings.'.$fieldKey)
                                            <p class="text-sm text-rose-300 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif

                @if($canManageGroup)
                    <div class="pt-2 flex flex-wrap items-center gap-3">
                        <button wire:click="updateGroup" wire:loading.attr="disabled" wire:target="updateGroup" class="admin-primary-btn min-w-32">
                            <span wire:loading.remove wire:target="updateGroup">Save Changes</span>
                            <span wire:loading.flex wire:target="updateGroup" class="items-center gap-2">
                                <x-admin.icon name="spinner" class="h-4 w-4 animate-spin" />
                                Saving...
                            </span>
                        </button>

                        @if($testTarget)
                            <button wire:click="testConnection" wire:loading.attr="disabled" wire:target="testConnection" class="admin-ghost-btn whitespace-nowrap">
                                <span wire:loading.remove wire:target="testConnection">Test connection</span>
                                <span wire:loading.flex wire:target="testConnection" class="items-center gap-2">
                                    <x-admin.icon name="spinner" class="h-4 w-4 animate-spin" />
                                    Testing...
                                </span>
                            </button>
                            <span class="text-xs text-admin-muted">Save first, then test.</span>
                        @endif
                    </div>
                @endif

                @if($recentAudits->isNotEmpty() || auth()->user()?->can('settings.view'))
                    <div class="pt-6 mt-4 border-t border-admin-stroke">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-semibold uppercase tracking-wider text-admin-muted">Recent Activity</h4>
                            <span class="text-[10px] text-admin-muted/60 uppercase tracking-widest">Global Audit Log</span>
                        </div>

                        <div class="space-y-2 max-h-72 overflow-auto pr-1 admin-scrollbar">
                            @forelse($recentAudits as $audit)
                                <div class="admin-row-item group/audit hover:border-primary-100/30 transition-colors" wire:key="settings-audit-{{ $audit->id }}">
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
                @endif
            </section>
        </div>
    @endif
</div>
