<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\InteractsWithAdminToast;
use App\Models\Setting;
use App\Models\SettingAudit;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Index extends Component
{
    use InteractsWithAdminToast;

    public const GROUP_DEFINITIONS = [
        'mail' => [
            '_meta' => [
                'icon' => 'mail',
                'description' => 'System & Touchpoint Email Account Designation Settings (cPanel SMTP transport configured in .env).',
            ],
            'from_name' => ['label' => 'System From Name', 'placeholder' => 'WestHub Healthcare', 'required' => true],
            'from_address' => ['label' => 'Default From Address', 'type' => 'email', 'placeholder' => 'no-reply@westhubhealthcare.com', 'required' => true],
            'appointments_from_address' => ['label' => 'Appointments Contact Email', 'type' => 'email', 'placeholder' => 'appointments@westhubhealthcare.com'],
            'applications_from_address' => ['label' => 'Careers & Applications Email', 'type' => 'email', 'placeholder' => 'careers@westhubhealthcare.com'],
            'enquiries_from_address' => ['label' => 'Enquiries & Contact Email', 'type' => 'email', 'placeholder' => 'info@westhubhealthcare.com'],
            'newsletter_from_address' => ['label' => 'Newsletter & Broadcast Email', 'type' => 'email', 'placeholder' => 'newsletter@westhubhealthcare.com'],
        ],
        'appointments' => [
            '_meta' => [
                'icon' => 'calendar',
                'description' => 'Appointments and scheduling configuration.',
            ],
            'calendly_url' => ['label' => 'Calendly Appointment Scheduling URL', 'type' => 'url', 'placeholder' => 'https://calendly.com/westhub/appointment'],
        ],
        'contact' => [
            '_meta' => [
                'icon' => 'phone',
                'description' => 'Public contact information displayed across website headers, bars, and metadata.',
            ],
            'public_phone' => ['label' => 'Public Phone Number', 'type' => 'text', 'placeholder' => '+1 2246250423'],
            'public_email' => ['label' => 'Public Contact Email', 'type' => 'email', 'placeholder' => 'info@westhubhealthcare.com'],
        ],
        'password' => [
            '_meta' => [
                'icon' => 'lock',
                'description' => 'Update your personal account security and password credentials.',
            ],
            'current_password' => ['label' => 'Current Password', 'type' => 'password', 'required' => true],
            'new_password' => ['label' => 'New Password', 'type' => 'password', 'required' => true],
            'new_password_confirmation' => ['label' => 'Confirm New Password', 'type' => 'password', 'required' => true],
        ],
    ];

    public bool $readyToLoad = true;
    public string $group = 'mail';
    public array $settings = [];

    public function mount(): void
    {
        if (! auth()->user()->can('access_settings')) {
            $this->group = 'password';
        }
        $this->loadGroup();
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
        if (! auth()->user()->can('access_settings')) {
            $this->group = 'password';
        }
        $this->loadGroup();
    }

    public function setGroup(string $group): void
    {
        if (! array_key_exists($group, self::GROUP_DEFINITIONS)) {
            return;
        }

        if (! auth()->user()->can('access_settings') && $group !== 'password') {
            return;
        }

        $this->group = $group;
        $this->loadGroup();
    }

    public function updateGroup(): void
    {
        if (! $this->readyToLoad) {
            return;
        }

        if ($this->group === 'password') {
            $this->updatePassword();
            return;
        }

        $validated = $this->validate($this->settingsRules());
        $fields = self::GROUP_DEFINITIONS[$this->group] ?? [];

        foreach ($fields as $key => $meta) {
            if ($key === '_meta') continue;

            $isEncrypted = (bool) ($meta['encrypted'] ?? false);
            $newValue = $this->normalizeInputValue($validated['settings'][$key] ?? null);

            $existing = Setting::query()
                ->where('group', $this->group)
                ->where('key', $key)
                ->first();

            $oldValue = $existing ? $this->decodeStoredValue($existing) : null;

            if ($oldValue === $newValue) {
                continue;
            }

            $stored = Setting::updateOrCreate(
                ['group' => $this->group, 'key' => $key],
                [
                    'value' => $this->encodeValueForStorage($newValue, $isEncrypted),
                    'is_encrypted' => $isEncrypted,
                    'type' => (string) ($meta['type'] ?? 'string'),
                    'updated_by' => auth()->id(),
                ]
            );

            SettingAudit::create([
                'setting_id' => $stored->id,
                'actor_id' => auth()->id(),
                'action' => $existing ? 'updated' : 'created',
                'old_value' => $this->auditValue($oldValue, $isEncrypted),
                'new_value' => $this->auditValue($newValue, $isEncrypted),
                'changed_at' => now(),
            ]);
        }

        $this->toastSuccess('Settings group saved.', 'Settings');
        $this->loadGroup();
    }

    protected function updatePassword(): void
    {
        $validated = $this->validate([
            'settings.current_password' => ['required', 'current_password'],
            'settings.new_password' => ['required', 'confirmed', Password::defaults()],
        ], [], [
            'settings.current_password' => 'current password',
            'settings.new_password' => 'new password',
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['settings']['new_password']),
        ]);

        $this->settings['current_password'] = '';
        $this->settings['new_password'] = '';
        $this->settings['new_password_confirmation'] = '';

        $this->toastSuccess('Password updated successfully.', 'Security');
    }

    protected function loadGroup(): void
    {
        if (! $this->readyToLoad) {
            return;
        }

        if ($this->group === 'password') {
            $this->settings = [
                'current_password' => '',
                'new_password' => '',
                'new_password_confirmation' => '',
            ];
            return;
        }

        $persisted = Setting::query()
            ->where('group', $this->group)
            ->get(['key', 'value', 'is_encrypted'])
            ->mapWithKeys(function (Setting $setting): array {
                return [$setting->key => $this->decodeStoredValue($setting) ?? ''];
            })
            ->toArray();

        $fields = self::GROUP_DEFINITIONS[$this->group] ?? [];
        $hydrated = [];

        foreach ($fields as $key => $meta) {
            if ($key === '_meta') continue;
            $hydrated[$key] = $persisted[$key] ?? '';
        }

        $this->settings = $hydrated;
    }

    protected function settingsRules(): array
    {
        $rules = [];
        $fields = self::GROUP_DEFINITIONS[$this->group] ?? [];

        foreach ($fields as $key => $meta) {
            if ($key === '_meta') continue;

            $fieldRules = ['nullable', 'string', 'max:5000'];

            if (($meta['required'] ?? false) === true) {
                $fieldRules = ['required', 'string', 'max:5000'];
            }

            if (($meta['type'] ?? null) === 'email') {
                $fieldRules[] = 'email:rfc';
            }

            if (($meta['type'] ?? null) === 'url') {
                $fieldRules[] = 'url';
            }

            $rules["settings.$key"] = $fieldRules;
        }

        return $rules;
    }

    protected function normalizeInputValue(mixed $value): ?string
    {
        $normalized = is_null($value) ? null : trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    protected function encodeValueForStorage(?string $value, bool $encrypted): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return $encrypted ? Crypt::encryptString($value) : $value;
    }

    protected function decodeStoredValue(Setting $setting): ?string
    {
        if (is_null($setting->value)) {
            return null;
        }

        if (! $setting->is_encrypted) {
            return (string) $setting->value;
        }

        try {
            return Crypt::decryptString((string) $setting->value);
        } catch (DecryptException) {
            return null;
        }
    }

    protected function auditValue(?string $value, bool $encrypted): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return $encrypted ? '[encrypted]' : $value;
    }

    public function render()
    {
        $allGroups = array_keys(self::GROUP_DEFINITIONS);
        $groups = auth()->user()->can('access_settings') 
            ? $allGroups 
            : ['password'];

        $currentFields = self::GROUP_DEFINITIONS[$this->group] ?? [];
        $recentAudits = SettingAudit::query()->with('actor', 'setting')->latest('changed_at')->take(12)->get();

        return view('livewire.admin.settings.index', compact('groups', 'currentFields', 'recentAudits'))
            ->layout('layouts.admin');
    }
}
