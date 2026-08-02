<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Models\Setting;
use App\Models\SettingAudit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_group_update_persists_values_and_writes_audits(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Setting::create([
            'group' => 'email',
            'key' => 'from_address',
            'value' => 'old@example.com',
            'is_encrypted' => false,
            'updated_by' => $user->id,
        ]);

        Livewire::test(SettingsIndex::class)
            ->call('loadData')
            ->call('setGroup', 'email')
            ->set('settings.from_name', 'WestHub Ops')
            ->set('settings.from_address', 'new@example.com')
            ->set('settings.smtp_password', 'super-secret')
            ->call('updateGroup')
            ->assertSet('feedbackMessage', 'Settings group saved.');

        $this->assertDatabaseHas('settings', [
            'group' => 'email',
            'key' => 'from_name',
            'value' => 'WestHub Ops',
        ]);

        $this->assertDatabaseHas('settings', [
            'group' => 'email',
            'key' => 'from_address',
            'value' => 'new@example.com',
        ]);

        $smtpPassword = Setting::query()
            ->where('group', 'email')
            ->where('key', 'smtp_password')
            ->first();

        $this->assertNotNull($smtpPassword);
        $this->assertTrue((bool) $smtpPassword->is_encrypted);
        $this->assertNotSame('super-secret', $smtpPassword->value);

        $this->assertDatabaseHas('setting_audits', [
            'action' => 'updated',
            'old_value' => 'old@example.com',
            'new_value' => 'new@example.com',
        ]);

        $this->assertDatabaseHas('setting_audits', [
            'action' => 'created',
            'new_value' => '[encrypted]',
        ]);

        $this->assertGreaterThanOrEqual(3, SettingAudit::count());
    }
}
