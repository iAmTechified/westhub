<div
    class="space-y-4"
    wire:init="loadData"
    x-data="{
        selectedCountyIdLocal: @js($selectedCountyId),
        selectedCountyNameLocal: @js(optional($counties->firstWhere('id', $selectedCountyId))->name),
        countiesLocal: @js($counties->map(fn ($county) => ['id' => $county->id, 'name' => $county->name])->values()->all()),
        townshipsLocal: @js($townships->map(fn ($township) => [
            'id' => $township->id,
            'county_id' => $township->county_id,
            'name' => $township->name,
            'sort_order' => $township->sort_order,
            'is_active' => (bool) $township->is_active,
        ])->values()->all()),
        isTownshipsLoading: false,
        townshipAbortController: null,
        townshipsEndpointBase: @js(url('/admin/locations/counties')),
        countyIsSelected(id) {
            return Number(this.selectedCountyIdLocal) === Number(id);
        },
        handleCountySelected(countyId, countyName = null) {
            if (!countyId) {
                this.selectedCountyIdLocal = null;
                this.selectedCountyNameLocal = null;
                this.townshipsLocal = [];
                this.abortTownshipRequest();
                this.isTownshipsLoading = false;
                return;
            }

            const normalizedId = Number(countyId);
            this.selectedCountyIdLocal = normalizedId;
            const county = this.countiesLocal.find((item) => Number(item.id) === normalizedId);
            if (!county && countyName) {
                this.countiesLocal.push({ id: normalizedId, name: countyName });
            }
            this.selectedCountyNameLocal = countyName ?? county?.name ?? this.selectedCountyNameLocal;
        },
        handleCountyDeleted(countyId) {
            const normalizedId = Number(countyId);
            this.countiesLocal = this.countiesLocal.filter((item) => Number(item.id) !== normalizedId);
            if (Number(this.selectedCountyIdLocal) === normalizedId) {
                this.selectedCountyIdLocal = null;
                this.selectedCountyNameLocal = null;
                this.townshipsLocal = [];
                this.abortTownshipRequest();
                this.isTownshipsLoading = false;
            }
        },
        abortTownshipRequest() {
            if (this.townshipAbortController) {
                this.townshipAbortController.abort();
                this.townshipAbortController = null;
            }
        },
        async refreshTownships(countyId = null) {
            const resolvedCountyId = countyId ? Number(countyId) : Number(this.selectedCountyIdLocal);
            if (!resolvedCountyId) {
                this.townshipsLocal = [];
                this.abortTownshipRequest();
                this.isTownshipsLoading = false;
                return;
            }

            this.abortTownshipRequest();
            this.isTownshipsLoading = true;
            const controller = new AbortController();
            this.townshipAbortController = controller;

            try {
                const response = await fetch(`${this.townshipsEndpointBase}/${resolvedCountyId}/townships`, {
                    method: 'GET',
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error(`Failed to load townships for county ${resolvedCountyId}`);
                }

                const payload = await response.json();
                if (controller !== this.townshipAbortController) {
                    return;
                }

                if (Number(this.selectedCountyIdLocal) !== resolvedCountyId) {
                    return;
                }

                this.townshipsLocal = Array.isArray(payload.data) ? payload.data : [];
            } catch (error) {
                if (error.name !== 'AbortError') {
                    this.townshipsLocal = [];
                }
            } finally {
                if (controller === this.townshipAbortController) {
                    this.townshipAbortController = null;
                    this.isTownshipsLoading = false;
                }
            }
        },
        selectCounty(county) {
            this.selectedCountyIdLocal = Number(county.id);
            this.selectedCountyNameLocal = county.name;
            this.refreshTownships(county.id);
        },
    }"
    x-on:locations-county-selected.window="handleCountySelected($event.detail.countyId ?? null, $event.detail.countyName ?? null)"
    x-on:locations-county-deleted.window="handleCountyDeleted($event.detail.countyId ?? null)"
    x-on:locations-refresh-townships.window="refreshTownships($event.detail.countyId ?? selectedCountyIdLocal)"
>
    <div class="glass-card p-5 relative z-10">
        <h2 class="inline-flex items-center gap-2 text-2xl font-semibold">
            <x-admin.icon name="location" class="h-5 w-5 text-primary-100" />
            Locations
        </h2>
        <p class="text-admin-muted mt-1">Manage county and township records only.</p>
    </div>

    @if(! $readyToLoad)
        <div class="grid gap-4 xl:grid-cols-[.85fr_1.15fr]">
            <div class="glass-card p-4 space-y-3">
                <div class="admin-skeleton h-8 w-1/3"></div>
                <div class="admin-skeleton h-12"></div>
                <div class="admin-skeleton h-12"></div>
                <div class="admin-skeleton h-12"></div>
            </div>
            <div class="glass-card p-4 space-y-3">
                <div class="admin-skeleton h-10"></div>
                <div class="admin-skeleton h-36"></div>
                <div class="admin-skeleton h-10 w-1/2"></div>
            </div>
        </div>
    @else
        <div class="grid gap-4 xl:grid-cols-[.95fr_1.05fr]">
            <div class="glass-card p-4 relative z-20 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="font-semibold">Counties</h3>
                    <button
                        type="button"
                        wire:click="openCreateCountyModal"
                        wire:loading.attr="disabled"
                        wire:target="openCreateCountyModal"
                        class="admin-primary-btn h-9 px-3 text-xs"
                    >
                        <span wire:loading.remove wire:target="openCreateCountyModal">Add County</span>
                        <span wire:loading wire:target="openCreateCountyModal">Opening...</span>
                    </button>
                </div>

                <div class="space-y-2 max-h-[520px] overflow-auto pr-1">
                    @forelse($counties as $county)
                        <div class="admin-row-item" :class="countyIsSelected({{ $county->id }}) ? 'ring-1 ring-primary-100' : ''">
                            <button
                                type="button"
                                class="text-left flex-1 min-w-0"
                                x-on:click="selectCounty({ id: {{ $county->id }}, name: @js($county->name) })"
                            >
                                <p class="truncate">{{ $county->name }}</p>
                                <p class="text-xs text-admin-muted">{{ $county->townships_count }} townships</p>
                            </button>
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    wire:click="openEditCountyModal({{ $county->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="openEditCountyModal({{ $county->id }})"
                                    class="admin-ghost-btn h-8 px-2 text-xs"
                                >
                                    <span wire:loading.remove wire:target="openEditCountyModal({{ $county->id }})">Edit</span>
                                    <span wire:loading wire:target="openEditCountyModal({{ $county->id }})">...</span>
                                </button>
                                <button
                                    type="button"
                                    wire:click="promptDeleteCounty({{ $county->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="promptDeleteCounty({{ $county->id }})"
                                    class="admin-ghost-btn h-8 px-2 text-xs !text-rose-300"
                                >
                                    <span wire:loading.remove wire:target="promptDeleteCounty({{ $county->id }})">Delete</span>
                                    <span wire:loading wire:target="promptDeleteCounty({{ $county->id }})">...</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="admin-empty-state p-5">
                            <p class="text-sm text-admin-muted">No counties yet. Create your first county.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="glass-card p-4 space-y-3 relative z-30 overflow-visible">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="font-semibold">Townships</h3>
                    <button
                        type="button"
                        x-on:click="$wire.openCreateTownshipModal(selectedCountyIdLocal)"
                        x-bind:disabled="!selectedCountyIdLocal"
                        wire:loading.attr="disabled"
                        wire:target="openCreateTownshipModal"
                        class="admin-primary-btn h-9 px-3 text-xs"
                    >
                        <span wire:loading.remove wire:target="openCreateTownshipModal">Add Township</span>
                        <span wire:loading wire:target="openCreateTownshipModal">Opening...</span>
                    </button>
                </div>

                <div x-show="isTownshipsLoading" class="space-y-3">
                    <div class="admin-row-item">
                        <div class="admin-skeleton h-4 w-40"></div>
                        <div class="admin-skeleton h-4 w-12"></div>
                    </div>
                    <div class="space-y-2">
                        <div class="admin-row-item">
                            <div class="space-y-2 flex-1">
                                <div class="admin-skeleton h-4 w-48"></div>
                                <div class="admin-skeleton h-3 w-36"></div>
                            </div>
                        </div>
                        <div class="admin-row-item">
                            <div class="space-y-2 flex-1">
                                <div class="admin-skeleton h-4 w-44"></div>
                                <div class="admin-skeleton h-3 w-32"></div>
                            </div>
                        </div>
                        <div class="admin-row-item">
                            <div class="space-y-2 flex-1">
                                <div class="admin-skeleton h-4 w-52"></div>
                                <div class="admin-skeleton h-3 w-40"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="!isTownshipsLoading" class="space-y-3">
                    <div x-show="!selectedCountyIdLocal" class="admin-empty-state p-5">
                        <p class="text-sm text-admin-muted">Select a county to manage its townships.</p>
                    </div>

                    <template x-if="selectedCountyIdLocal">
                        <div class="space-y-3">
                            <div class="admin-row-item">
                                <span x-text="selectedCountyNameLocal || 'Selected county'"></span>
                                <span class="text-xs text-admin-muted" x-text="'#' + selectedCountyIdLocal"></span>
                            </div>

                            <div class="space-y-2 max-h-[420px] overflow-auto pr-1">
                                <template x-if="townshipsLocal.length === 0">
                                    <div class="admin-empty-state p-5">
                                        <p class="text-sm text-admin-muted">No townships for this county yet.</p>
                                    </div>
                                </template>

                                <template x-for="township in townshipsLocal" :key="township.id">
                                    <div class="admin-row-item">
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate" x-text="township.name"></p>
                                            <p class="text-xs text-admin-muted">
                                                Order <span x-text="township.sort_order"></span> |
                                                <span x-text="township.is_active ? 'Active' : 'Inactive'"></span>
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button
                                                type="button"
                                                x-on:click="$wire.openEditTownshipModal(township.id)"
                                                wire:loading.attr="disabled"
                                                wire:target="openEditTownshipModal"
                                                class="admin-ghost-btn h-8 px-2 text-xs"
                                            >
                                                <span wire:loading.remove wire:target="openEditTownshipModal">Edit</span>
                                                <span wire:loading wire:target="openEditTownshipModal">...</span>
                                            </button>
                                            <button
                                                type="button"
                                                x-on:click="$wire.promptDeleteTownship(township.id)"
                                                wire:loading.attr="disabled"
                                                wire:target="promptDeleteTownship"
                                                class="admin-ghost-btn h-8 px-2 text-xs !text-rose-300"
                                            >
                                                <span wire:loading.remove wire:target="promptDeleteTownship">Delete</span>
                                                <span wire:loading wire:target="promptDeleteTownship">...</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    @endif

    @if($showCountyModal)
        <div class="admin-modal-backdrop" wire:click="closeCountyModal"></div>
        <section class="admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="county-modal-title">
            <header class="flex items-center justify-between gap-3 mb-5">
                <h3 id="county-modal-title" class="text-xl font-semibold">{{ $editingCountyId ? 'Edit County' : 'Add County' }}</h3>
                <button type="button" wire:click="closeCountyModal" class="admin-icon-btn h-10 w-10" title="Close">
                    <x-admin.icon name="close" class="h-4 w-4" />
                </button>
            </header>

            <form wire:submit="saveCounty" class="space-y-4">
                <div>
                    <label class="admin-label" for="county-name">County Name <span class="text-rose-400">*</span></label>
                    <input id="county-name" type="text" class="admin-input w-full" wire:model="countyName" placeholder="e.g. Harris County">
                    @error('countyName') <p class="admin-input-feedback is-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="admin-label" for="county-description">Description</label>
                    <textarea id="county-description" class="admin-input w-full h-24 resize-none" wire:model="countyDescription" placeholder="Optional description"></textarea>
                    @error('countyDescription') <p class="admin-input-feedback is-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="admin-label" for="county-sort-order">Sort Order</label>
                        <input id="county-sort-order" type="number" min="0" class="admin-input w-full" wire:model="countySortOrder">
                        @error('countySortOrder') <p class="admin-input-feedback is-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-end">
                        <label class="admin-row-item w-full cursor-pointer">
                            <span>Active</span>
                            <input type="checkbox" wire:model="countyIsActive">
                        </label>
                    </div>
                </div>

                <footer class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeCountyModal" class="admin-ghost-btn">Cancel</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveCounty" class="admin-primary-btn gap-2">
                        <span wire:loading.remove wire:target="saveCounty">{{ $editingCountyId ? 'Update County' : 'Create County' }}</span>
                        <span wire:loading wire:target="saveCounty" class="tiny-orb-loader"></span>
                    </button>
                </footer>
            </form>
        </section>
    @endif

    @if($showTownshipModal)
        <div class="admin-modal-backdrop" wire:click="closeTownshipModal"></div>
        <section class="admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="township-modal-title">
            <header class="flex items-center justify-between gap-3 mb-5">
                <h3 id="township-modal-title" class="text-xl font-semibold">{{ $editingTownshipId ? 'Edit Township' : 'Add Township' }}</h3>
                <button type="button" wire:click="closeTownshipModal" class="admin-icon-btn h-10 w-10" title="Close">
                    <x-admin.icon name="close" class="h-4 w-4" />
                </button>
            </header>

            <form wire:submit="saveTownship" class="space-y-4">
                <div>
                    <label class="admin-label" for="township-county-id">County <span class="text-rose-400">*</span></label>
                    <x-admin.select id="township-county-id" wire:model="townshipCountyId">
                        <x-admin.option value="">Select county</x-admin.option>
                        @foreach($counties as $county)
                            <x-admin.option value="{{ $county->id }}">{{ $county->name }}</x-admin.option>
                        @endforeach
                    </x-admin.select>
                </div>

                <div>
                    <label class="admin-label" for="township-name">Township Name <span class="text-rose-400">*</span></label>
                    <input id="township-name" type="text" class="admin-input w-full" wire:model="townshipName" placeholder="e.g. Riverside Township">
                    @error('townshipName') <p class="admin-input-feedback is-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="admin-label" for="township-sort-order">Sort Order</label>
                        <input id="township-sort-order" type="number" min="0" class="admin-input w-full" wire:model="townshipSortOrder">
                        @error('townshipSortOrder') <p class="admin-input-feedback is-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-end">
                        <label class="admin-row-item w-full cursor-pointer">
                            <span>Active</span>
                            <input type="checkbox" wire:model="townshipIsActive">
                        </label>
                    </div>
                </div>

                <footer class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeTownshipModal" class="admin-ghost-btn">Cancel</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveTownship" class="admin-primary-btn gap-2">
                        <span wire:loading.remove wire:target="saveTownship">{{ $editingTownshipId ? 'Update Township' : 'Create Township' }}</span>
                        <span wire:loading wire:target="saveTownship" class="tiny-orb-loader"></span>
                    </button>
                </footer>
            </form>
        </section>
    @endif

    @if($showDeleteModal)
        <div class="admin-modal-backdrop" wire:click="cancelDelete"></div>
        <section class="admin-modal-panel max-w-md" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
            <header class="mb-4">
                <h3 id="delete-modal-title" class="text-xl font-semibold">Confirm Delete</h3>
            </header>
            <p class="text-admin-muted">
                Are you sure you want to delete
                <strong class="text-admin-ink">{{ $pendingDeleteLabel }}</strong>?
                @if($pendingDeleteType === 'county')
                    This will also delete all townships linked to this county.
                @endif
                This action cannot be undone.
            </p>
            <footer class="mt-5 flex items-center justify-end gap-2">
                <button type="button" wire:click="cancelDelete" class="admin-ghost-btn">Cancel</button>
                <button type="button" wire:click="confirmDelete" wire:loading.attr="disabled" wire:target="confirmDelete" class="admin-primary-btn bg-red-500 hover:bg-red-400 gap-2">
                    <span wire:loading.remove wire:target="confirmDelete">Delete</span>
                    <span wire:loading wire:target="confirmDelete" class="tiny-orb-loader"></span>
                </button>
            </footer>
        </section>
    @endif
</div>
