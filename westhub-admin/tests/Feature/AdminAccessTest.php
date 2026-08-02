<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_route_is_protected_by_auth_and_role(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();

        Role::findOrCreate('super_admin');
        $user->assignRole('super_admin');

        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
    }
}
