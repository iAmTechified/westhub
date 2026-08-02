<div class="space-y-5" wire:init="loadData">
    <header class="glass-card p-6 lg:p-8">
        <p class="text-xs uppercase tracking-[0.22em] text-admin-muted">Content</p>
        <h2 class="mt-2 inline-flex items-center gap-2 text-3xl font-semibold">
            <x-admin.icon name="pulse" class="h-6 w-6 text-primary-100" />
            Care Services
        </h2>
        <p class="mt-2 text-admin-muted">Manage care service groups and their associated items. Organise services by group, control visibility and sort order.</p>
    </header>

    @if($loadError)
        <div class="glass-card p-6 text-center space-y-4">
            <p class="text-admin-muted">{{ $loadErrorMessage }}</p>
            <button type="button" wire:click="retryLoading" class="admin-ghost-btn gap-2">
                <x-admin.icon name="refresh" class="h-4 w-4" />
                Retry
            </button>
        </div>
    @else

    {{-- ── GROUPS SECTION ─────────────────────────────────────────────────── --}}
    <section class="space-y-3">
        <div class="glass-card p-5 space-y-4 relative z-50 overflow-visible">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-lg font-semibold">Service Groups</h3>
                <button type="button" wire:click="openCreateGroupModal" class="admin-primary-btn gap-2">
                    <x-admin.icon name="plus" class="h-4 w-4" />
                    New Group
                </button>
            </div>

            <div class="grid gap-3 md:grid-cols-[1fr_auto] items-end">
                <div class="admin-filter-field">
                    <label class="admin-label">Search Groups</label>
                    <div class="admin-input-wrap relative">
                        <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-admin-muted" />
                        <input type="text" class="admin-input pl-9" placeholder="Search groups…" wire:model.live.debounce.250ms="groupSearch">
                    </div>
                </div>
                <div class="admin-filter-field">
                    <label class="admin-label">Group Status</label>
                    <x-admin.select wire:model.live="groupStatus" placeholder="Filter status">
                        <x-admin.option value="all">All statuses</x-admin.option>
                        <x-admin.option value="draft">Draft</x-admin.option>
                        <x-admin.option value="published">Published</x-admin.option>
                        <x-admin.option value="archived">Archived</x-admin.option>
                    </x-admin.select>
                </div>
            </div>
        </div>

        <div class="glass-card relative z-40">
            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full min-w-[700px] text-sm">
                    <thead class="bg-white/5 text-admin-muted">
                        <tr>
                            <th class="text-left p-3">Name</th>
                            <th class="text-left p-3">Status</th>
                            <th class="text-left p-3">Items</th>
                            <th class="text-left p-3">Sort</th>
                            <th class="text-left p-3">Active</th>
                            <th class="text-right p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $group)
                            <tr class="border-t border-admin-stroke hover:bg-white/5 transition-colors duration-[140ms]">
                                <td class="p-3">
                                    <p class="font-medium">{{ $group->name }}</p>
                                    @if($group->description)
                                        <p class="text-xs text-admin-muted mt-0.5 line-clamp-1">{{ $group->description }}</p>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <span class="admin-status-badge is-{{ $group->status }}">{{ str($group->status)->title() }}</span>
                                </td>
                                <td class="p-3 text-admin-muted">{{ $group->items_count }}</td>
                                <td class="p-3">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button" wire:click="moveGroupUp({{ $group->id }})" class="admin-icon-btn h-7 w-7" title="Move up">
                                            <x-admin.icon name="chevron-up" class="h-3.5 w-3.5" />
                                        </button>
                                        <span class="text-admin-muted w-6 text-center text-xs">{{ $group->sort_order }}</span>
                                        <button type="button" wire:click="moveGroupDown({{ $group->id }})" class="admin-icon-btn h-7 w-7" title="Move down">
                                            <x-admin.icon name="chevron-down" class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <button
                                        type="button"
                                        wire:click="toggleGroupActive({{ $group->id }})"
                                        class="admin-toggle {{ $group->is_active ? 'is-on' : 'is-off' }}"
                                        title="{{ $group->is_active ? 'Deactivate' : 'Activate' }}"
                                    >
                                        <span class="sr-only">{{ $group->is_active ? 'Active' : 'Inactive' }}</span>
                                    </button>
                                </td>
                                <td class="p-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button" wire:click="openEditGroupModal({{ $group->id }})" class="admin-icon-btn" title="Edit group">
                                            <x-admin.icon name="edit" class="h-4 w-4" />
                                        </button>
                                        <button type="button" wire:click="openCreateItemModal({{ $group->id }})" class="admin-ghost-btn h-8 px-2 text-xs gap-1" title="Add item to group">
                                            <x-admin.icon name="plus" class="h-3.5 w-3.5" />
                                            Item
                                        </button>
                                        <button type="button" wire:click="promptDeleteGroup({{ $group->id }})" class="admin-icon-btn text-red-400 hover:text-red-300" title="Delete group">
                                            <x-admin.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-10 text-center text-admin-muted">No service groups found. Create one to get started.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- ── ITEMS SECTION ───────────────────────────────────────────────────── --}}
    <section class="space-y-3">
        <div class="glass-card p-5 space-y-4 relative z-30 overflow-visible">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-lg font-semibold">Service Items</h3>
                <button type="button" wire:click="openCreateItemModal()" class="admin-primary-btn gap-2">
                    <x-admin.icon name="plus" class="h-4 w-4" />
                    New Item
                </button>
            </div>

            <div class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 items-end">
                <div class="admin-filter-field">
                    <label class="admin-label">Search Items</label>
                    <div class="admin-input-wrap relative">
                        <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-admin-muted" />
                        <input type="text" class="admin-input pl-9" placeholder="Search items…" wire:model.live.debounce.250ms="itemSearch">
                    </div>
                </div>
                <div class="admin-filter-field">
                    <label class="admin-label">Service Group</label>
                    <x-admin.select wire:model.live="itemGroupFilter" placeholder="Filter group">
                        <x-admin.option value="all">All groups</x-admin.option>
                        @foreach($groups as $g)
                            <x-admin.option value="{{ $g->id }}">{{ $g->name }}</x-admin.option>
                        @endforeach
                    </x-admin.select>
                </div>
                <div class="admin-filter-field">
                    <label class="admin-label">Item Status</label>
                    <x-admin.select wire:model.live="itemStatus" placeholder="Filter status">
                        <x-admin.option value="all">All statuses</x-admin.option>
                        <x-admin.option value="draft">Draft</x-admin.option>
                        <x-admin.option value="published">Published</x-admin.option>
                        <x-admin.option value="archived">Archived</x-admin.option>
                    </x-admin.select>
                </div>
            </div>
        </div>

        <div class="glass-card relative z-40">
            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full min-w-[800px] text-sm">
                    <thead class="bg-white/5 text-admin-muted">
                        <tr>
                            <th class="text-left p-3">Title</th>
                            <th class="text-left p-3">Group</th>
                            <th class="text-left p-3">Status</th>
                            <th class="text-left p-3">Sort</th>
                            <th class="text-left p-3">Active</th>
                            <th class="text-right p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr class="border-t border-admin-stroke hover:bg-white/5 transition-colors duration-[140ms]">
                                <td class="p-3">
                                    <p class="font-medium">{{ $item->title }}</p>
                                    @if($item->subtitle)
                                        <p class="text-xs text-admin-muted mt-0.5">{{ $item->subtitle }}</p>
                                    @endif
                                </td>
                                <td class="p-3 text-admin-muted">{{ $item->group?->name ?? '—' }}</td>
                                <td class="p-3">
                                    <span class="admin-status-badge is-{{ $item->status }}">{{ str($item->status)->title() }}</span>
                                </td>
                                <td class="p-3">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button" wire:click="moveItemUp({{ $item->id }})" class="admin-icon-btn h-7 w-7" title="Move up">
                                            <x-admin.icon name="chevron-up" class="h-3.5 w-3.5" />
                                        </button>
                                        <span class="text-admin-muted w-6 text-center text-xs">{{ $item->sort_order }}</span>
                                        <button type="button" wire:click="moveItemDown({{ $item->id }})" class="admin-icon-btn h-7 w-7" title="Move down">
                                            <x-admin.icon name="chevron-down" class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <button
                                        type="button"
                                        wire:click="toggleItemActive({{ $item->id }})"
                                        class="admin-toggle {{ $item->is_active ? 'is-on' : 'is-off' }}"
                                        title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}"
                                    >
                                        <span class="sr-only">{{ $item->is_active ? 'Active' : 'Inactive' }}</span>
                                    </button>
                                </td>
                                <td class="p-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button" wire:click="openEditItemModal({{ $item->id }})" class="admin-icon-btn" title="Edit item">
                                            <x-admin.icon name="edit" class="h-4 w-4" />
                                        </button>
                                        <button type="button" wire:click="promptDeleteItem({{ $item->id }})" class="admin-icon-btn text-red-400 hover:text-red-300" title="Delete item">
                                            <x-admin.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-10 text-center text-admin-muted">No service items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    @endif

    {{-- ── GROUP MODAL ─────────────────────────────────────────────────────── --}}
    @if($showGroupModal)
        <div class="admin-modal-backdrop" wire:click="closeGroupModal"></div>
        <section class="admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="group-modal-title">
            <header class="flex items-center justify-between gap-3 mb-5">
                <h3 id="group-modal-title" class="text-xl font-semibold">
                    {{ $editingGroupId ? 'Edit Group' : 'New Group' }}
                </h3>
                <button type="button" wire:click="closeGroupModal" class="admin-icon-btn h-10 w-10" title="Close">
                    <x-admin.icon name="close" class="h-4 w-4" />
                </button>
            </header>

            <form wire:submit="saveGroup" class="space-y-4">
                <div>
                    <label class="block text-sm text-admin-muted mb-1" for="group-name">Name <span class="text-red-400">*</span></label>
                    <input id="group-name" type="text" class="admin-input w-full" wire:model="groupName" placeholder="e.g. Residential Care">
                    @error('groupName') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm text-admin-muted mb-1" for="group-description">Description</label>
                    <textarea id="group-description" class="admin-input w-full h-24 resize-none" wire:model="groupDescription" placeholder="Optional short description…"></textarea>
                    @error('groupDescription') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm text-admin-muted mb-1" for="group-status">Status <span class="text-red-400">*</span></label>
                        <x-admin.select id="group-status" wire:model="groupFormStatus" placeholder="Status">
                            <x-admin.option value="draft">Draft</x-admin.option>
                            <x-admin.option value="published">Published</x-admin.option>
                            <x-admin.option value="archived">Archived</x-admin.option>
                        </x-admin.select>
                        @error('groupFormStatus') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-admin-muted mb-1" for="group-sort-order">Sort Order</label>
                        <input id="group-sort-order" type="number" min="0" class="admin-input w-full" wire:model="groupSortOrder">
                        @error('groupSortOrder') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col justify-end">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="admin-checkbox" wire:model="groupIsActive">
                            <span class="text-sm">Active</span>
                        </label>
                    </div>
                </div>

                <footer class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeGroupModal" class="admin-ghost-btn">Cancel</button>
                    <button type="submit" class="admin-primary-btn gap-2">
                        <span wire:loading.remove wire:target="saveGroup">{{ $editingGroupId ? 'Update Group' : 'Create Group' }}</span>
                        <span wire:loading wire:target="saveGroup" class="tiny-orb-loader"></span>
                    </button>
                </footer>
            </form>
        </section>
    @endif

    {{-- ── ITEM MODAL ──────────────────────────────────────────────────────── --}}
    @if($showItemModal)
        <div class="admin-modal-backdrop" wire:click="closeItemModal"></div>
        <section class="admin-modal-panel max-h-[90vh] overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="item-modal-title">
            <header class="flex items-center justify-between gap-3 mb-5">
                <h3 id="item-modal-title" class="text-xl font-semibold">
                    {{ $editingItemId ? 'Edit Item' : 'New Item' }}
                </h3>
                <button type="button" wire:click="closeItemModal" class="admin-icon-btn h-10 w-10" title="Close">
                    <x-admin.icon name="close" class="h-4 w-4" />
                </button>
            </header>

            <form wire:submit="saveItem" class="space-y-4">
                <div>
                    <label class="block text-sm text-admin-muted mb-1" for="item-group">Group <span class="text-red-400">*</span></label>
                    <x-admin.select id="item-group" wire:model="itemGroupId" placeholder="Select Group">
                        <x-admin.option value="">Select a group…</x-admin.option>
                        @foreach($groups as $g)
                            <x-admin.option value="{{ $g->id }}">{{ $g->name }}</x-admin.option>
                        @endforeach
                    </x-admin.select>
                    @error('itemGroupId') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm text-admin-muted mb-1" for="item-service">Linked Service</label>
                    <x-admin.select id="item-service" wire:model="itemServiceId" placeholder="Select Service">
                        <x-admin.option value="">None</x-admin.option>
                        @foreach($services as $svc)
                            <x-admin.option value="{{ $svc->id }}">{{ $svc->name }}</x-admin.option>
                        @endforeach
                    </x-admin.select>
                    @error('itemServiceId') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm text-admin-muted mb-1" for="item-title">Title <span class="text-red-400">*</span></label>
                    <input id="item-title" type="text" class="admin-input w-full" wire:model="itemTitle" placeholder="e.g. Dementia Care">
                    @error('itemTitle') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm text-admin-muted mb-1" for="item-subtitle">Subtitle</label>
                    <input id="item-subtitle" type="text" class="admin-input w-full" wire:model="itemSubtitle" placeholder="Short tagline">
                    @error('itemSubtitle') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm text-admin-muted mb-1" for="item-description">Description</label>
                    <textarea id="item-description" class="admin-input w-full h-24 resize-none" wire:model="itemDescription" placeholder="Full description…"></textarea>
                    @error('itemDescription') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm text-admin-muted mb-1" for="item-icon">Icon</label>
                    <input id="item-icon" type="text" class="admin-input w-full" wire:model="itemIcon" placeholder="e.g. heart, brain">
                    @error('itemIcon') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm text-admin-muted mb-1" for="item-status">Status <span class="text-red-400">*</span></label>
                        <x-admin.select id="item-status" wire:model="itemFormStatus" placeholder="Status">
                            <x-admin.option value="draft">Draft</x-admin.option>
                            <x-admin.option value="published">Published</x-admin.option>
                            <x-admin.option value="archived">Archived</x-admin.option>
                        </x-admin.select>
                        @error('itemFormStatus') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-admin-muted mb-1" for="item-sort-order">Sort Order</label>
                        <input id="item-sort-order" type="number" min="0" class="admin-input w-full" wire:model="itemSortOrder">
                        @error('itemSortOrder') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col justify-end">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="admin-checkbox" wire:model="itemIsActive">
                            <span class="text-sm">Active</span>
                        </label>
                    </div>
                </div>

                <footer class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeItemModal" class="admin-ghost-btn">Cancel</button>
                    <button type="submit" class="admin-primary-btn gap-2">
                        <span wire:loading.remove wire:target="saveItem">{{ $editingItemId ? 'Update Item' : 'Create Item' }}</span>
                        <span wire:loading wire:target="saveItem" class="tiny-orb-loader"></span>
                    </button>
                </footer>
            </form>
        </section>
    @endif

    {{-- ── DELETE CONFIRM MODAL ────────────────────────────────────────────── --}}
    @if($showDeleteModal)
        <div class="admin-modal-backdrop" wire:click="cancelDelete"></div>
        <section class="admin-modal-panel max-w-md" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
            <header class="mb-4">
                <h3 id="delete-modal-title" class="text-xl font-semibold">Confirm Delete</h3>
            </header>
            <p class="text-admin-muted">
                Are you sure you want to delete
                <strong class="text-admin-ink">{{ $pendingDeleteLabel }}</strong>?
                @if($pendingDeleteType === 'group')
                    This will also remove all items within this group.
                @endif
                This action cannot be undone.
            </p>
            <footer class="mt-5 flex items-center justify-end gap-2">
                <button type="button" wire:click="cancelDelete" class="admin-ghost-btn">Cancel</button>
                <button type="button" wire:click="confirmDelete" class="admin-primary-btn bg-red-500 hover:bg-red-400 gap-2">
                    <span wire:loading.remove wire:target="confirmDelete">Delete</span>
                    <span wire:loading wire:target="confirmDelete" class="tiny-orb-loader"></span>
                </button>
            </footer>
        </section>
    @endif
</div>
