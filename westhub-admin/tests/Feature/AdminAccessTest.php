<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_login_screen(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_a_user_without_a_role_is_signed_out_rather_than_left_on_a_403(): void
    {
        $this->seedRoles();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_a_super_admin_can_open_every_module(): void
    {
        $this->actingAsAdmin();

        foreach ([
            'admin.dashboard', 'admin.articles.index', 'admin.articles.create', 'admin.join-requests.index',
            'admin.appointments.index', 'admin.promo-claims.index', 'admin.gallery.index', 'admin.care-services.index',
            'admin.locations.index', 'admin.users.index', 'admin.users.create', 'admin.subscribers.index', 'admin.settings.index',
        ] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_marketing_runs_the_campaign_but_cannot_touch_care_operations_or_users(): void
    {
        $this->actingAsAdmin(AdminPermissions::MARKETING);

        $this->get(route('admin.promo-claims.index'))->assertOk();
        $this->get(route('admin.subscribers.index'))->assertOk();
        $this->get(route('admin.articles.create'))->assertOk();

        $this->get(route('admin.appointments.index'))->assertForbidden();
        $this->get(route('admin.join-requests.index'))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_ops_works_appointments_but_cannot_write_articles(): void
    {
        $this->actingAsAdmin(AdminPermissions::OPS);

        $this->get(route('admin.appointments.index'))->assertOk();
        $this->get(route('admin.join-requests.index'))->assertOk();
        $this->get(route('admin.promo-claims.index'))->assertOk();

        $this->get(route('admin.articles.create'))->assertForbidden();
    }

    public function test_only_a_super_admin_can_manage_users(): void
    {
        foreach ([AdminPermissions::EDITOR, AdminPermissions::REVIEWER, AdminPermissions::OPS, AdminPermissions::MARKETING] as $role) {
            $this->actingAsAdmin($role);

            $this->get(route('admin.users.index'))->assertForbidden();
            $this->get(route('admin.users.create'))->assertForbidden();
        }
    }

    public function test_every_admin_can_reach_settings_to_change_their_own_password(): void
    {
        $this->actingAsAdmin(AdminPermissions::REVIEWER);

        $this->get(route('admin.settings.index'))->assertOk();
    }
}
