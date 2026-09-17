<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The migration that moves existing admins off `access_*` direct permissions
 * and onto roles. Nobody should lose access to a screen they could already use.
 */
class LegacyPermissionMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function runMigration(): void
    {
        $migration = require database_path('migrations/2026_09_17_090000_migrate_admin_access_permissions_to_roles.php');
        $migration->up();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function legacyUser(array $permissions): User
    {
        $user = User::factory()->create();

        foreach ($permissions as $name) {
            $user->givePermissionTo(Permission::findOrCreate($name, 'web'));
        }

        return $user;
    }

    public function test_a_content_person_becomes_an_editor(): void
    {
        $user = $this->legacyUser(['access_articles', 'access_gallery', 'access_care_services']);

        $this->runMigration();

        $user->refresh();
        $this->assertSame([AdminPermissions::EDITOR], $user->getRoleNames()->all());
        $this->assertTrue($user->can('articles.view'));
        $this->assertTrue($user->can('gallery.view'));
        $this->assertTrue($user->can('care_services.view'));
    }

    public function test_an_operations_person_becomes_ops(): void
    {
        $user = $this->legacyUser(['access_appointments', 'access_applications', 'access_subscribers']);

        $this->runMigration();

        $user->refresh();
        $this->assertSame([AdminPermissions::OPS], $user->getRoleNames()->all());
        $this->assertTrue($user->can('appointments.view'));
        $this->assertTrue($user->can('join_requests.view'));
        $this->assertTrue($user->can('subscribers.view'));
    }

    public function test_on_a_tie_the_least_privileged_role_is_chosen(): void
    {
        // articles.view alone is granted by editor, reviewer, ops and marketing.
        $user = $this->legacyUser(['access_articles']);

        $this->runMigration();

        $this->assertSame([AdminPermissions::REVIEWER], $user->fresh()->getRoleNames()->all());
    }

    public function test_someone_who_already_has_a_role_is_left_alone(): void
    {
        $this->seedRoles();

        $user = $this->legacyUser(['access_appointments']);
        $user->assignRole(AdminPermissions::MARKETING);

        $this->runMigration();

        $this->assertSame([AdminPermissions::MARKETING], $user->fresh()->getRoleNames()->all());
    }

    public function test_the_retired_permissions_are_removed(): void
    {
        $this->legacyUser(['access_articles', 'access_settings']);

        $this->runMigration();

        $this->assertSame(0, Permission::query()->where('name', 'like', 'access\_%')->count());
    }

    public function test_it_does_nothing_on_a_database_that_never_had_the_old_scheme(): void
    {
        $user = User::factory()->create();

        $this->runMigration();

        $this->assertSame([], $user->fresh()->getRoleNames()->all());
    }
}
