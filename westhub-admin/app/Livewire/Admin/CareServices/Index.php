<?php

namespace App\Livewire\Admin\CareServices;

use Illuminate\Support\Facades\Gate;
use App\Livewire\Admin\Concerns\InteractsWithAdminToast;
use App\Models\CareServiceGroup;
use App\Models\CareServiceItem;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Index extends Component
{
    use InteractsWithAdminToast;

    public bool $readyToLoad = true;
    public bool $loadError = false;
    public ?string $loadErrorMessage = null;

    public string $groupSearch = '';
    public string $groupStatus = 'all';

    public string $itemSearch = '';
    public string $itemStatus = 'all';
    public string $itemGroupFilter = 'all';

    public bool $showGroupModal = false;
    public ?int $editingGroupId = null;
    public string $groupName = '';
    public ?string $groupDescription = null;
    public string $groupFormStatus = 'draft';
    public bool $groupIsActive = true;
    public int $groupSortOrder = 0;

    public bool $showItemModal = false;
    public ?int $editingItemId = null;
    public ?int $itemGroupId = null;
    public ?int $itemServiceId = null;
    public string $itemTitle = '';
    public ?string $itemSubtitle = null;
    public ?string $itemDescription = null;
    public ?string $itemIcon = null;
    public string $itemFormStatus = 'draft';
    public bool $itemIsActive = true;
    public int $itemSortOrder = 0;

    public bool $showDeleteModal = false;
    public ?string $pendingDeleteType = null;
    public ?int $pendingDeleteId = null;
    public ?string $pendingDeleteLabel = null;

    public function mount(): void
    {
        Gate::authorize('care_services.view');
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
        $this->loadError = false;
        $this->loadErrorMessage = null;
    }

    public function retryLoading(): void
    {
        $this->loadData();
    }

    public function openCreateGroupModal(): void
    {
        Gate::authorize('care_services.manage');

        $this->resetGroupForm();
        $this->showGroupModal = true;
    }

    public function openEditGroupModal(int $id): void
    {
        Gate::authorize('care_services.manage');

        $group = CareServiceGroup::query()->findOrFail($id);

        $this->editingGroupId = $group->id;
        $this->groupName = $group->name;
        $this->groupDescription = $group->description;
        $this->groupFormStatus = $group->status;
        $this->groupIsActive = (bool) $group->is_active;
        $this->groupSortOrder = (int) $group->sort_order;
        $this->showGroupModal = true;
    }

    public function closeGroupModal(): void
    {
        $this->showGroupModal = false;
        $this->resetGroupForm();
    }

    public function saveGroup(): void
    {
        Gate::authorize('care_services.manage');

        $this->validate([
            'groupName' => ['required', 'string', 'max:255'],
            'groupDescription' => ['nullable', 'string'],
            'groupFormStatus' => ['required', 'in:draft,published,archived'],
            'groupSortOrder' => ['required', 'integer', 'min:0'],
        ]);

        $group = $this->editingGroupId
            ? CareServiceGroup::query()->findOrFail($this->editingGroupId)
            : new CareServiceGroup();

        $group->fill([
            'name' => $this->groupName,
            'description' => $this->groupDescription,
            'status' => $this->groupFormStatus,
            'is_active' => $this->groupIsActive,
            'sort_order' => $this->groupSortOrder,
            'published_at' => $this->groupFormStatus === 'published' ? ($group->published_at ?? now()) : null,
        ]);
        $group->save();

        $this->toastSuccess($this->editingGroupId ? 'Care service group updated.' : 'Care service group created.', 'Care Services');
        $this->closeGroupModal();
    }

    public function openCreateItemModal(?int $groupId = null): void
    {
        Gate::authorize('care_services.manage');

        $this->resetItemForm();
        $this->itemGroupId = $groupId;
        $this->showItemModal = true;
    }

    public function openEditItemModal(int $id): void
    {
        Gate::authorize('care_services.manage');

        $item = CareServiceItem::query()->findOrFail($id);

        $this->editingItemId = $item->id;
        $this->itemGroupId = $item->care_service_group_id;
        $this->itemServiceId = $item->service_id;
        $this->itemTitle = $item->title;
        $this->itemSubtitle = $item->subtitle;
        $this->itemDescription = $item->description;
        $this->itemIcon = $item->icon;
        $this->itemFormStatus = $item->status;
        $this->itemIsActive = (bool) $item->is_active;
        $this->itemSortOrder = (int) $item->sort_order;
        $this->showItemModal = true;
    }

    public function closeItemModal(): void
    {
        $this->showItemModal = false;
        $this->resetItemForm();
    }

    public function saveItem(): void
    {
        Gate::authorize('care_services.manage');

        $this->validate([
            'itemGroupId' => ['required', 'exists:care_service_groups,id'],
            'itemServiceId' => ['nullable', 'exists:services,id'],
            'itemTitle' => ['required', 'string', 'max:255'],
            'itemSubtitle' => ['nullable', 'string', 'max:255'],
            'itemDescription' => ['nullable', 'string'],
            'itemIcon' => ['nullable', 'string', 'max:255'],
            'itemFormStatus' => ['required', 'in:draft,published,archived'],
            'itemSortOrder' => ['required', 'integer', 'min:0'],
        ]);

        $item = $this->editingItemId
            ? CareServiceItem::query()->findOrFail($this->editingItemId)
            : new CareServiceItem();

        $item->fill([
            'care_service_group_id' => $this->itemGroupId,
            'service_id' => $this->itemServiceId,
            'title' => $this->itemTitle,
            'subtitle' => $this->itemSubtitle,
            'description' => $this->itemDescription,
            'icon' => $this->itemIcon,
            'status' => $this->itemFormStatus,
            'is_active' => $this->itemIsActive,
            'sort_order' => $this->itemSortOrder,
            'published_at' => $this->itemFormStatus === 'published' ? ($item->published_at ?? now()) : null,
        ]);
        $item->save();

        $this->toastSuccess($this->editingItemId ? 'Care service item updated.' : 'Care service item created.', 'Care Services');
        $this->closeItemModal();
    }

    public function toggleGroupActive(int $id): void
    {
        Gate::authorize('care_services.manage');

        $group = CareServiceGroup::query()->findOrFail($id);
        $group->update(['is_active' => ! $group->is_active]);
        $this->toastSuccess('Group active state updated.', 'Care Services');
    }

    public function toggleItemActive(int $id): void
    {
        Gate::authorize('care_services.manage');

        $item = CareServiceItem::query()->findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);
        $this->toastSuccess('Item active state updated.', 'Care Services');
    }

    public function setGroupStatus(int $id, string $status): void
    {
        Gate::authorize('care_services.manage');

        if (! in_array($status, ['draft', 'published', 'archived'], true)) {
            return;
        }

        $group = CareServiceGroup::query()->findOrFail($id);
        $group->update([
            'status' => $status,
            'published_at' => $status === 'published' ? ($group->published_at ?? now()) : null,
        ]);

        $this->toastSuccess('Group status updated.', 'Care Services');
    }

    public function setItemStatus(int $id, string $status): void
    {
        Gate::authorize('care_services.manage');

        if (! in_array($status, ['draft', 'published', 'archived'], true)) {
            return;
        }

        $item = CareServiceItem::query()->findOrFail($id);
        $item->update([
            'status' => $status,
            'published_at' => $status === 'published' ? ($item->published_at ?? now()) : null,
        ]);

        $this->toastSuccess('Item status updated.', 'Care Services');
    }

    public function moveGroupUp(int $id): void
    {
        Gate::authorize('care_services.manage');

        $group = CareServiceGroup::query()->findOrFail($id);

        $previous = CareServiceGroup::query()
            ->where(function (Builder $builder) use ($group) {
                $builder->where('sort_order', '<', $group->sort_order)
                    ->orWhere(function (Builder $inner) use ($group) {
                        $inner->where('sort_order', $group->sort_order)
                            ->where('id', '<', $group->id);
                    });
            })
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->first();

        if (! $previous) {
            return;
        }

        $currentOrder = (int) $group->sort_order;
        $previousOrder = (int) $previous->sort_order;

        if ($previousOrder === $currentOrder) {
            $group->update(['sort_order' => $currentOrder > 0 ? $currentOrder - 1 : 0]);
        } else {
            $group->update(['sort_order' => $previousOrder]);
            $previous->update(['sort_order' => $currentOrder]);
        }

        $this->toastSuccess('Group order updated.', 'Care Services');
    }

    public function moveGroupDown(int $id): void
    {
        Gate::authorize('care_services.manage');

        $group = CareServiceGroup::query()->findOrFail($id);

        $next = CareServiceGroup::query()
            ->where(function (Builder $builder) use ($group) {
                $builder->where('sort_order', '>', $group->sort_order)
                    ->orWhere(function (Builder $inner) use ($group) {
                        $inner->where('sort_order', $group->sort_order)
                            ->where('id', '>', $group->id);
                    });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if (! $next) {
            return;
        }

        $currentOrder = (int) $group->sort_order;
        $nextOrder = (int) $next->sort_order;

        if ($nextOrder === $currentOrder) {
            $group->update(['sort_order' => $currentOrder + 1]);
        } else {
            $group->update(['sort_order' => $nextOrder]);
            $next->update(['sort_order' => $currentOrder]);
        }

        $this->toastSuccess('Group order updated.', 'Care Services');
    }

    public function moveItemUp(int $id): void
    {
        Gate::authorize('care_services.manage');

        $item = CareServiceItem::query()->findOrFail($id);

        $previous = CareServiceItem::query()
            ->where('care_service_group_id', $item->care_service_group_id)
            ->where(function (Builder $builder) use ($item) {
                $builder->where('sort_order', '<', $item->sort_order)
                    ->orWhere(function (Builder $inner) use ($item) {
                        $inner->where('sort_order', $item->sort_order)
                            ->where('id', '<', $item->id);
                    });
            })
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->first();

        if (! $previous) {
            return;
        }

        $currentOrder = (int) $item->sort_order;
        $previousOrder = (int) $previous->sort_order;

        if ($previousOrder === $currentOrder) {
            $item->update(['sort_order' => $currentOrder > 0 ? $currentOrder - 1 : 0]);
        } else {
            $item->update(['sort_order' => $previousOrder]);
            $previous->update(['sort_order' => $currentOrder]);
        }

        $this->toastSuccess('Item order updated.', 'Care Services');
    }

    public function moveItemDown(int $id): void
    {
        Gate::authorize('care_services.manage');

        $item = CareServiceItem::query()->findOrFail($id);

        $next = CareServiceItem::query()
            ->where('care_service_group_id', $item->care_service_group_id)
            ->where(function (Builder $builder) use ($item) {
                $builder->where('sort_order', '>', $item->sort_order)
                    ->orWhere(function (Builder $inner) use ($item) {
                        $inner->where('sort_order', $item->sort_order)
                            ->where('id', '>', $item->id);
                    });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if (! $next) {
            return;
        }

        $currentOrder = (int) $item->sort_order;
        $nextOrder = (int) $next->sort_order;

        if ($nextOrder === $currentOrder) {
            $item->update(['sort_order' => $currentOrder + 1]);
        } else {
            $item->update(['sort_order' => $nextOrder]);
            $next->update(['sort_order' => $currentOrder]);
        }

        $this->toastSuccess('Item order updated.', 'Care Services');
    }

    public function promptDeleteGroup(int $id): void
    {
        Gate::authorize('care_services.manage');

        $group = CareServiceGroup::query()->findOrFail($id);

        $this->pendingDeleteType = 'group';
        $this->pendingDeleteId = $group->id;
        $this->pendingDeleteLabel = $group->name;
        $this->showDeleteModal = true;
    }

    public function promptDeleteItem(int $id): void
    {
        Gate::authorize('care_services.manage');

        $item = CareServiceItem::query()->findOrFail($id);

        $this->pendingDeleteType = 'item';
        $this->pendingDeleteId = $item->id;
        $this->pendingDeleteLabel = $item->title;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->pendingDeleteType = null;
        $this->pendingDeleteId = null;
        $this->pendingDeleteLabel = null;
    }

    public function confirmDelete(): void
    {
        Gate::authorize('care_services.manage');

        if (! $this->pendingDeleteType || ! $this->pendingDeleteId) {
            return;
        }

        if ($this->pendingDeleteType === 'group') {
            CareServiceGroup::query()->findOrFail($this->pendingDeleteId)->delete();
            $this->toastSuccess('Care service group deleted.', 'Care Services');
        }

        if ($this->pendingDeleteType === 'item') {
            CareServiceItem::query()->findOrFail($this->pendingDeleteId)->delete();
            $this->toastSuccess('Care service item deleted.', 'Care Services');
        }

        $this->cancelDelete();
    }

    public function render()
    {
        $groups = collect();
        $items = collect();
        $services = collect();

        try {
            if ($this->readyToLoad) {
                $groups = CareServiceGroup::query()
                    ->withCount('items')
                    ->when($this->groupSearch !== '', function (Builder $query) {
                        $query->where(function (Builder $builder) {
                            $builder->where('name', 'like', '%'.$this->groupSearch.'%')
                                ->orWhere('description', 'like', '%'.$this->groupSearch.'%');
                        });
                    })
                    ->when($this->groupStatus !== 'all', fn (Builder $query) => $query->where('status', $this->groupStatus))
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();

                $items = CareServiceItem::query()
                    ->with(['group', 'service'])
                    ->when($this->itemSearch !== '', function (Builder $query) {
                        $query->where(function (Builder $builder) {
                            $builder->where('title', 'like', '%'.$this->itemSearch.'%')
                                ->orWhere('subtitle', 'like', '%'.$this->itemSearch.'%')
                                ->orWhere('description', 'like', '%'.$this->itemSearch.'%');
                        });
                    })
                    ->when($this->itemStatus !== 'all', fn (Builder $query) => $query->where('status', $this->itemStatus))
                    ->when($this->itemGroupFilter !== 'all', fn (Builder $query) => $query->where('care_service_group_id', (int) $this->itemGroupFilter))
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();

                $services = Service::query()->orderBy('name')->get();
            }

            $this->loadError = false;
            $this->loadErrorMessage = null;
        } catch (\Throwable $exception) {
            report($exception);
            $this->loadError = true;
            $this->loadErrorMessage = 'Unable to load care services right now. Please retry.';
        }

        return view('livewire.admin.care-services.index', compact('groups', 'items', 'services'))
            ->layout('layouts.admin');
    }

    protected function resetGroupForm(): void
    {
        $this->editingGroupId = null;
        $this->groupName = '';
        $this->groupDescription = null;
        $this->groupFormStatus = 'draft';
        $this->groupIsActive = true;
        $this->groupSortOrder = 0;
        $this->resetValidation();
    }

    protected function resetItemForm(): void
    {
        $this->editingItemId = null;
        $this->itemGroupId = null;
        $this->itemServiceId = null;
        $this->itemTitle = '';
        $this->itemSubtitle = null;
        $this->itemDescription = null;
        $this->itemIcon = null;
        $this->itemFormStatus = 'draft';
        $this->itemIsActive = true;
        $this->itemSortOrder = 0;
        $this->resetValidation();
    }
}
