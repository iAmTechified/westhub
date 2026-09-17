<?php

namespace Database\Seeders;

use App\Support\AdminPermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates every admin role and permission from AdminPermissions.
 *
 * Idempotent: safe to run on every deploy. It never touches which users hold
 * which roles.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(AdminPermissions::all()) as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // super_admin holds no permission rows: it is granted everything by the
        // Gate::before bypass in AppServiceProvider.
        Role::findOrCreate(AdminPermissions::SUPER_ADMIN, 'web');

        foreach (AdminPermissions::roleMatrix() as $roleName => $permissions) {
            Role::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
