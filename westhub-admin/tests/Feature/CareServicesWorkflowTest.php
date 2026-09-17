<?php

namespace Tests\Feature;

use App\Livewire\Admin\CareServices\Index as CareServicesIndex;
use App\Models\CareServiceGroup;
use App\Models\CareServiceItem;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CareServicesWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_care_services_buttons_backed_by_livewire_methods_work(): void
    {
        $user = $this->actingAsAdmin();

        $service = Service::query()->create([
            'name' => 'Personal Care',
            'status' => 'published',
            'is_active' => true,
            'sort_order' => 1,
            'published_at' => now(),
        ]);

        Livewire::test(CareServicesIndex::class)
            ->call('openCreateGroupModal')
            ->assertSet('showGroupModal', true)
            ->set('groupName', 'Home Assistance')
            ->set('groupDescription', 'Support at home')
            ->set('groupFormStatus', 'published')
            ->set('groupIsActive', true)
            ->set('groupSortOrder', 1)
            ->call('saveGroup')
            ->assertDispatched('admin-toast', fn ($event, $params) => ($params['message'] ?? null) === 'Care service group created.');

        $group = CareServiceGroup::query()->where('name', 'Home Assistance')->firstOrFail();

        Livewire::test(CareServicesIndex::class)
            ->call('openCreateItemModal', $group->id)
            ->assertSet('showItemModal', true)
            ->set('itemGroupId', $group->id)
            ->set('itemServiceId', $service->id)
            ->set('itemTitle', 'Medication Reminder')
            ->set('itemSubtitle', 'Daily support')
            ->set('itemDescription', 'Reminder support')
            ->set('itemIcon', 'pill')
            ->set('itemFormStatus', 'published')
            ->set('itemIsActive', true)
            ->set('itemSortOrder', 1)
            ->call('saveItem')
            ->assertDispatched('admin-toast', fn ($event, $params) => ($params['message'] ?? null) === 'Care service item created.');

        $item = CareServiceItem::query()->where('title', 'Medication Reminder')->firstOrFail();

        Livewire::test(CareServicesIndex::class)
            ->call('toggleGroupActive', $group->id)
            ->assertDispatched('admin-toast', fn ($event, $params) => ($params['message'] ?? null) === 'Group active state updated.')
            ->call('toggleItemActive', $item->id)
            ->assertDispatched('admin-toast', fn ($event, $params) => ($params['message'] ?? null) === 'Item active state updated.')
            ->call('setGroupStatus', $group->id, 'archived')
            ->assertDispatched('admin-toast', fn ($event, $params) => ($params['message'] ?? null) === 'Group status updated.')
            ->call('setItemStatus', $item->id, 'archived')
            ->assertDispatched('admin-toast', fn ($event, $params) => ($params['message'] ?? null) === 'Item status updated.');

        $group->refresh();
        $item->refresh();

        $this->assertFalse((bool) $group->is_active);
        $this->assertFalse((bool) $item->is_active);
        $this->assertSame('archived', $group->status);
        $this->assertSame('archived', $item->status);

        $groupA = CareServiceGroup::query()->create([
            'name' => 'Group A',
            'status' => 'draft',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $groupB = CareServiceGroup::query()->create([
            'name' => 'Group B',
            'status' => 'draft',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $itemA = CareServiceItem::query()->create([
            'care_service_group_id' => $groupA->id,
            'service_id' => $service->id,
            'title' => 'Item A',
            'status' => 'draft',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $itemB = CareServiceItem::query()->create([
            'care_service_group_id' => $groupA->id,
            'service_id' => $service->id,
            'title' => 'Item B',
            'status' => 'draft',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Livewire::test(CareServicesIndex::class)
            ->call('moveGroupDown', $groupA->id)
            ->assertDispatched('admin-toast', fn ($event, $params) => ($params['message'] ?? null) === 'Group order updated.')
            ->call('moveItemDown', $itemA->id)
            ->assertDispatched('admin-toast', fn ($event, $params) => ($params['message'] ?? null) === 'Item order updated.')
            ->call('promptDeleteItem', $itemB->id)
            ->assertSet('showDeleteModal', true)
            ->call('confirmDelete')
            ->assertDispatched('admin-toast', fn ($event, $params) => ($params['message'] ?? null) === 'Care service item deleted.')
            ->call('promptDeleteGroup', $groupB->id)
            ->assertSet('showDeleteModal', true)
            ->call('confirmDelete')
            ->assertDispatched('admin-toast', fn ($event, $params) => ($params['message'] ?? null) === 'Care service group deleted.');

        $this->assertDatabaseMissing('care_service_items', ['id' => $itemB->id]);
        $this->assertDatabaseMissing('care_service_groups', ['id' => $groupB->id]);
    }
}
