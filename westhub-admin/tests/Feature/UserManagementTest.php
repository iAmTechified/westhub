<?php

namespace Tests\Feature;

use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Livewire\Admin\Users\Studio as UsersStudio;
use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_user_assigns_exactly_one_role(): void
    {
        $this->actingAsAdmin();

        Livewire::test(UsersStudio::class)
            ->set('name', 'Casey Ops')
            ->set('email', 'casey@westhub.test')
            ->set('password', 'a-Strong-Passw0rd!')
            ->set('password_confirmation', 'a-Strong-Passw0rd!')
            ->set('role', AdminPermissions::OPS)
            ->call('save')
            ->assertHasNoErrors();

        $user = User::query()->where('email', 'casey@westhub.test')->firstOrFail();

        $this->assertSame([AdminPermissions::OPS], $user->getRoleNames()->all());
        $this->assertTrue($user->can('appointments.view'));
        $this->assertFalse($user->can('users.manage'));
    }

    public function test_a_role_is_required(): void
    {
        $this->actingAsAdmin();

        Livewire::test(UsersStudio::class)
            ->set('name', 'No Role')
            ->set('email', 'norole@westhub.test')
            ->set('password', 'a-Strong-Passw0rd!')
            ->set('password_confirmation', 'a-Strong-Passw0rd!')
            ->call('save')
            ->assertHasErrors(['role' => 'required']);
    }

    public function test_editing_clears_leftover_direct_permissions_from_the_old_scheme(): void
    {
        $this->actingAsAdmin();

        $legacy = Permission::findOrCreate('access_articles', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo($legacy);

        Livewire::test(UsersStudio::class, ['user' => $user])
            ->set('role', AdminPermissions::EDITOR)
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame([AdminPermissions::EDITOR], $user->getRoleNames()->all());
        $this->assertCount(0, $user->getDirectPermissions());
    }

    public function test_the_last_super_admin_cannot_be_demoted(): void
    {
        $self = $this->actingAsAdmin();

        Livewire::test(UsersStudio::class, ['user' => $self])
            ->set('role', AdminPermissions::EDITOR)
            ->call('save')
            ->assertHasErrors(['role']);

        $this->assertTrue($self->fresh()->hasRole(AdminPermissions::SUPER_ADMIN));
    }

    public function test_you_cannot_delete_yourself_even_by_setting_the_id_from_the_browser(): void
    {
        $self = $this->actingAsAdmin();

        // deletingUserId is a public property; set it directly, as a browser could.
        Livewire::test(UsersIndex::class)
            ->set('deletingUserId', $self->id)
            ->call('deleteUser');

        $this->assertNotNull(User::find($self->id));
    }

    public function test_a_super_admin_can_delete_another_super_admin_when_one_remains(): void
    {
        $this->actingAsAdmin();

        $onlyOtherAdmin = User::factory()->create();
        $onlyOtherAdmin->assignRole(AdminPermissions::SUPER_ADMIN);

        // Two super admins: deleting one is allowed.
        Livewire::test(UsersIndex::class)
            ->set('deletingUserId', $onlyOtherAdmin->id)
            ->call('deleteUser');

        $this->assertNull(User::find($onlyOtherAdmin->id));
    }

    public function test_the_users_list_flags_accounts_that_cannot_sign_in(): void
    {
        $this->actingAsAdmin();

        User::factory()->create(['name' => 'Roleless Person']);

        Livewire::test(UsersIndex::class)
            ->call('loadData')
            ->assertSee('Roleless Person')
            ->assertSee('No role');
    }
}
