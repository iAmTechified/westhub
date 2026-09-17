<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Models\Setting;
use App\Models\SettingAudit;
use App\Support\AdminPermissions;
use App\Support\SettingsCrypto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_a_group_persists_values_and_writes_audits(): void
    {
        $user = $this->actingAsAdmin();

        Setting::create([
            'group' => 'mail',
            'key' => 'from_address',
            'value' => 'old@example.com',
            'is_encrypted' => false,
            'updated_by' => $user->id,
        ]);

        Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->call('setGroup', 'mail')
            ->set('settings.from_name', 'WestHub Ops')
            ->set('settings.from_address', 'new@example.com')
            ->call('updateGroup')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings', ['group' => 'mail', 'key' => 'from_name', 'value' => 'WestHub Ops']);
        $this->assertDatabaseHas('settings', ['group' => 'mail', 'key' => 'from_address', 'value' => 'new@example.com']);

        $this->assertDatabaseHas('setting_audits', [
            'action' => 'updated',
            'old_value' => 'old@example.com',
            'new_value' => 'new@example.com',
        ]);

        $this->assertGreaterThanOrEqual(2, SettingAudit::count());
    }

    public function test_a_service_account_key_is_stored_encrypted_and_never_sent_back_to_the_browser(): void
    {
        $this->actingAsAdmin();

        $component = Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->call('setGroup', 'integrations')
            ->set('settings.google_sheets_spreadsheet_id', 'sheet-123')
            ->set('settings.google_sheets_private_key', '-----BEGIN PRIVATE KEY-----secret')
            ->call('updateGroup')
            ->assertHasNoErrors();

        $stored = Setting::query()->where('group', 'integrations')->where('key', 'google_sheets_private_key')->firstOrFail();

        $this->assertTrue((bool) $stored->is_encrypted);
        $this->assertStringNotContainsString('secret', $stored->value);
        $this->assertSame('-----BEGIN PRIVATE KEY-----secret', SettingsCrypto::decrypt($stored->value));

        $this->assertDatabaseHas('setting_audits', ['new_value' => '[encrypted]']);

        // After saving, the form reloads with the secret blanked out.
        $component->assertSet('settings.google_sheets_private_key', '');
    }

    public function test_saving_again_with_the_key_field_blank_keeps_the_existing_key(): void
    {
        $this->actingAsAdmin();

        Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->call('setGroup', 'integrations')
            ->set('settings.google_sheets_private_key', 'original-key')
            ->call('updateGroup')
            ->set('settings.google_sheets_spreadsheet_id', 'changed-id')
            ->call('updateGroup');

        $stored = Setting::query()->where('group', 'integrations')->where('key', 'google_sheets_private_key')->firstOrFail();

        $this->assertSame('original-key', SettingsCrypto::decrypt($stored->value));
    }

    /**
     * Livewire binds checkboxes with `checked = !!value`, and "0" is truthy in
     * JavaScript. A switched-off toggle must load as a real false, or it
     * renders ticked and gets switched back on by the next save.
     */
    public function test_a_switched_off_toggle_loads_as_false(): void
    {
        $this->actingAsAdmin();
        $this->setSettings('promotions', ['enabled' => '0']);

        Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->call('setGroup', 'promotions')
            ->assertSet('settings.enabled', false);
    }

    public function test_switching_the_booking_provider_changes_which_fields_are_shown(): void
    {
        $this->actingAsAdmin();

        $component = Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->call('setGroup', 'appointments')
            ->set('settings.provider', 'calendly');

        $this->assertArrayHasKey('calendly_url', $component->instance()->visibleFields());
        $this->assertArrayNotHasKey('google_calendar_id', $component->instance()->visibleFields());

        $component->set('settings.provider', 'google');

        $this->assertArrayHasKey('google_calendar_id', $component->instance()->visibleFields());
        $this->assertArrayNotHasKey('calendly_url', $component->instance()->visibleFields());
    }

    public function test_marketing_can_run_the_promo_but_cannot_change_credentials(): void
    {
        $this->actingAsAdmin(AdminPermissions::MARKETING);

        Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->call('setGroup', 'promotions')
            ->set('settings.headline', 'A new headline')
            ->call('updateGroup')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings', ['group' => 'promotions', 'key' => 'headline', 'value' => 'A new headline']);

        // Forcing the group to one they cannot manage is refused and writes nothing.
        Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->set('group', 'integrations')
            ->set('settings.google_sheets_spreadsheet_id', 'hijacked')
            ->call('updateGroup')
            ->assertForbidden();

        $this->assertDatabaseMissing('settings', ['key' => 'google_sheets_spreadsheet_id', 'value' => 'hijacked']);
    }

    /**
     * `group` is a public property, so a user could set it from the browser to
     * a group they are not allowed to save. The save must still be refused.
     */
    public function test_a_reviewer_cannot_save_site_settings_by_forcing_the_group(): void
    {
        $this->actingAsAdmin(AdminPermissions::REVIEWER);

        Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->set('group', 'mail')
            ->set('settings.from_address', 'attacker@example.com')
            ->call('updateGroup')
            ->assertForbidden();

        $this->assertDatabaseMissing('settings', ['key' => 'from_address', 'value' => 'attacker@example.com']);
    }

    /**
     * Reviewers can read the promo settings (they hold promos.view) but not
     * change them, and cannot see mail, booking, integration or contact settings.
     */
    public function test_a_reviewer_sees_promotions_read_only_and_their_own_password(): void
    {
        $this->actingAsAdmin(AdminPermissions::REVIEWER);

        Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->assertSet('group', 'promotions')
            ->assertViewHas('groups', ['promotions', 'password'])
            ->assertViewHas('canManageGroup', false);
    }

    /**
     * Regression: an editor has no settings permissions at all, but must still
     * be able to change their own password.
     */
    public function test_an_editor_with_no_settings_access_can_still_change_their_password(): void
    {
        $user = $this->actingAsAdmin(AdminPermissions::EDITOR);
        $user->forceFill(['password' => \Illuminate\Support\Facades\Hash::make('old-Passw0rd!')])->save();

        Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->assertSet('group', 'password')
            ->set('settings.current_password', 'old-Passw0rd!')
            ->set('settings.new_password', 'a-New-Passw0rd!')
            ->set('settings.new_password_confirmation', 'a-New-Passw0rd!')
            ->call('updateGroup')
            ->assertHasNoErrors();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('a-New-Passw0rd!', $user->fresh()->password));
    }
}
