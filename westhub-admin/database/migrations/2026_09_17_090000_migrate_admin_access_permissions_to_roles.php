<?php

use App\Support\AdminPermissions;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Moves admin access from the retired `access_*` direct permissions onto roles.
 *
 * Before: the Users screen granted each person individual `access_*`
 * permissions and no role. After: every admin holds exactly one role, and the
 * role decides what they can do.
 *
 * For each user who holds `access_*` permissions but no role, this assigns the
 * single role that covers the most of what they could already open, preferring
 * the least-privileged role on a tie. Anyone who already has a role is left as
 * they are. The `access_*` permissions are then removed.
 *
 * Safe to run on a database that never had the old scheme: it does nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('model_has_permissions')) {
            return;
        }

        // Make sure every new role and permission exists before assigning.
        (new RolesAndPermissionsSeeder)->run();

        $legacyNames = array_keys(AdminPermissions::legacyPermissionMap());

        $legacyPermissionIds = DB::table('permissions')
            ->whereIn('name', $legacyNames)
            ->pluck('id', 'name');

        if ($legacyPermissionIds->isEmpty()) {
            return;
        }

        $userModel = config('auth.providers.users.model', \App\Models\User::class);
        $roleIds = DB::table('roles')->pluck('id', 'name');

        $holders = DB::table('model_has_permissions')
            ->whereIn('permission_id', $legacyPermissionIds->values())
            ->where('model_type', $userModel)
            ->get(['model_id', 'permission_id'])
            ->groupBy('model_id');

        $idToName = $legacyPermissionIds->flip();
        $report = [];

        foreach ($holders as $userId => $rows) {
            $alreadyHasRole = DB::table('model_has_roles')
                ->where('model_type', $userModel)
                ->where('model_id', $userId)
                ->exists();

            $legacy = $rows->map(fn ($row) => $idToName[$row->permission_id] ?? null)->filter()->values()->all();

            if ($alreadyHasRole) {
                $report[] = "user #{$userId}: already had a role, left unchanged";
                continue;
            }

            $choice = AdminPermissions::bestRoleForLegacy($legacy);

            if ($choice['role'] === null || ! isset($roleIds[$choice['role']])) {
                $report[] = "user #{$userId}: no matching role for [" . implode(', ', $legacy) . '], needs manual review';
                continue;
            }

            DB::table('model_has_roles')->insert([
                'role_id' => $roleIds[$choice['role']],
                'model_type' => $userModel,
                'model_id' => $userId,
            ]);

            $note = $choice['missing'] === []
                ? 'full coverage'
                : 'NOT covered: ' . implode(', ', $choice['missing']);

            $report[] = "user #{$userId}: [" . implode(', ', $legacy) . "] -> {$choice['role']} ({$note})";
        }

        DB::table('model_has_permissions')->whereIn('permission_id', $legacyPermissionIds->values())->delete();
        DB::table('role_has_permissions')->whereIn('permission_id', $legacyPermissionIds->values())->delete();
        DB::table('permissions')->whereIn('id', $legacyPermissionIds->values())->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($report !== []) {
            Log::info("Admin access migrated from access_* permissions to roles:\n" . implode("\n", $report));
        }
    }

    public function down(): void
    {
        // Not reversible: the per-user permission grants are gone. Restore from
        // a database backup if the old scheme is ever needed again.
    }
};
