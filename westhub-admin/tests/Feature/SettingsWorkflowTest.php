<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Models\Setting;
use App\Models\SettingAudit;
use App\Support\AdminPermissions;
use App\Support\SettingsCrypto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
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
    public function test_the_sheets_method_switches_between_service_account_and_apps_script_fields(): void
    {
        $this->actingAsAdmin();

        $component = Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->call('setGroup', 'integrations');

        // Unset means service account, so existing setups keep their fields.
        $fields = $component->instance()->visibleFields();
        $this->assertArrayHasKey('google_sheets_private_key', $fields);
        $this->assertArrayNotHasKey('google_sheets_apps_script_url', $fields);

        $component->set('settings.google_sheets_method', 'apps_script');

        $fields = $component->instance()->visibleFields();
        $this->assertArrayHasKey('google_sheets_apps_script_url', $fields);
        $this->assertArrayHasKey('google_sheets_apps_script_secret', $fields);
        $this->assertArrayNotHasKey('google_sheets_private_key', $fields);
        $this->assertArrayNotHasKey('google_sheets_spreadsheet_id', $fields);
        // Tab names apply to both methods.
        $this->assertArrayHasKey('google_sheets_join_requests_tab', $fields);
    }

    public function test_an_apps_script_setup_saves_with_the_secret_encrypted(): void
    {
        $this->actingAsAdmin();

        $component = Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->call('setGroup', 'integrations')
            ->set('settings.google_sheets_method', 'apps_script')
            ->set('settings.google_sheets_apps_script_url', 'https://docs.google.com/spreadsheets/d/abc/edit')
            ->set('settings.google_sheets_apps_script_secret', 'shared-secret-123')
            ->call('updateGroup')
            ->assertHasErrors(['settings.google_sheets_apps_script_url' => 'regex']);

        $this->assertDatabaseMissing('settings', ['key' => 'google_sheets_apps_script_secret']);

        $component
            ->set('settings.google_sheets_apps_script_url', 'https://script.google.com/macros/s/AKfycbx_TEST-1/exec')
            ->call('updateGroup')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings', ['group' => 'integrations', 'key' => 'google_sheets_method', 'value' => 'apps_script']);

        $secret = Setting::query()->where('group', 'integrations')->where('key', 'google_sheets_apps_script_secret')->firstOrFail();
        $this->assertTrue((bool) $secret->is_encrypted);
        $this->assertSame('shared-secret-123', SettingsCrypto::decrypt($secret->value));

        $component->assertSet('settings.google_sheets_apps_script_secret', '');
    }

    public function test_the_google_booking_page_provider_takes_only_a_google_link(): void
    {
        $this->actingAsAdmin();

        $component = Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->call('setGroup', 'appointments')
            ->set('settings.provider', 'google_booking_page');

        $fields = $component->instance()->visibleFields();
        $this->assertArrayHasKey('google_booking_page_url', $fields);
        $this->assertArrayNotHasKey('google_service_account_private_key', $fields);
        $this->assertArrayNotHasKey('calendly_url', $fields);

        $component
            ->set('settings.google_booking_page_url', 'https://calendly.com/westhub/appointment')
            ->call('updateGroup')
            ->assertHasErrors(['settings.google_booking_page_url' => 'regex']);

        $component
            ->set('settings.google_booking_page_url', 'https://calendar.app.google/uXbq7kEYq2')
            ->call('updateGroup')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings', ['group' => 'appointments', 'key' => 'provider', 'value' => 'google_booking_page']);
        $this->assertDatabaseHas('settings', ['group' => 'appointments', 'key' => 'google_booking_page_url', 'value' => 'https://calendar.app.google/uXbq7kEYq2']);
    }
    public function test_an_unsaved_sheets_setup_shows_what_env_provides(): void
    {
        $this->actingAsAdmin();

        config()->set('services.google_sheets.method', 'apps_script');
        config()->set('services.google_sheets.apps_script_url', 'https://script.google.com/macros/s/AKfycbx_FROM-ENV/exec');
        config()->set('services.google_sheets.apps_script_secret', 'env-secret-never-shown');

        $component = Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->call('setGroup', 'integrations')
            // The select shows the method actually in use, so the right fields appear.
            ->assertSet('settings.google_sheets_method', 'apps_script')
            // Syncing is on when configured and never saved; the box must say so.
            ->assertSet('settings.google_sheets_enabled', true)
            // Text fields stay empty so saving does not copy .env into the database.
            ->assertSet('settings.google_sheets_apps_script_url', '')
            ->assertSee('https://script.google.com/macros/s/AKfycbx_FROM-ENV/exec')
            ->assertSee('GOOGLE_SHEETS_APPS_SCRIPT_SECRET')
            ->assertDontSee('env-secret-never-shown');

        $this->assertArrayHasKey('google_sheets_apps_script_url', $component->instance()->visibleFields());
    }

    public function test_a_saved_value_replaces_the_env_note(): void
    {
        $this->actingAsAdmin();

        config()->set('services.google_booking_page.url', 'https://calendar.app.google/FromEnv');

        Setting::create(['group' => 'appointments', 'key' => 'provider', 'value' => 'google_booking_page', 'is_encrypted' => false]);
        Setting::create(['group' => 'appointments', 'key' => 'google_booking_page_url', 'value' => 'https://calendar.app.google/Saved', 'is_encrypted' => false]);

        Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->call('setGroup', 'appointments')
            ->assertSet('settings.google_booking_page_url', 'https://calendar.app.google/Saved')
            ->assertDontSee('https://calendar.app.google/FromEnv');
    }

    public function test_connection_errors_show_technical_detail_only_in_debug_mode(): void
    {
        $this->actingAsAdmin();

        Setting::create(['group' => 'integrations', 'key' => 'google_sheets_method', 'value' => 'apps_script', 'is_encrypted' => false]);
        Setting::create(['group' => 'integrations', 'key' => 'google_sheets_apps_script_url', 'value' => 'https://script.google.com/macros/s/AKfycbx_T/exec', 'is_encrypted' => false]);
        Setting::create(['group' => 'integrations', 'key' => 'google_sheets_apps_script_secret', 'value' => 'secret', 'is_encrypted' => false]);

        Http::fake(fn () => throw new ConnectionException('cURL error 60: SSL certificate problem for https://script.googleusercontent.com/macros/echo?user_content_key=abc'));

        config()->set('app.debug', false);

        Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->call('setGroup', 'integrations')
            ->call('testConnection')
            ->assertSee('Could not reach Google Apps Script')
            ->assertDontSee('cURL error 60')
            ->assertDontSee('user_content_key');

        config()->set('app.debug', true);

        Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->call('setGroup', 'integrations')
            ->call('testConnection')
            ->assertSee('Could not reach Google Apps Script')
            ->assertSee('cURL error 60');
    }
}
