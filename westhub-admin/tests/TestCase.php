<?php

namespace Tests;

use App\Models\Setting;
use App\Models\User;
use App\Support\AdminPermissions;
use App\Support\SiteSettings;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        // Settings are memoised per request, and tests share one process.
        SiteSettings::flush();

        parent::tearDown();
    }

    /**
     * Create every admin role and permission. Any test that opens an admin
     * screen needs this, because each component authorizes on mount.
     */
    protected function seedRoles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * Sign in as a user holding one role. super_admin (the default) is granted
     * every ability through the Gate::before bypass.
     */
    protected function actingAsAdmin(string $role = AdminPermissions::SUPER_ADMIN): User
    {
        $this->seedRoles();

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user);

        return $user;
    }

    /**
     * Write settings straight to the shared table, as the admin does.
     *
     * @param  array<string, string|null>  $values
     */
    protected function setSettings(string $group, array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::query()->updateOrCreate(
                ['group' => $group, 'key' => $key],
                ['value' => $value, 'is_encrypted' => false, 'type' => 'string']
            );
        }

        SiteSettings::flush($group);
    }
}
