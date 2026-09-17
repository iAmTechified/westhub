<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\InteractsWithAdminToast;
use App\Models\Setting;
use App\Models\SettingAudit;
use App\Services\Appointments\AppointmentProviderManager;
use App\Services\Google\GoogleSheets;
use App\Support\SettingsCrypto;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Throwable;

class Index extends Component
{
    use InteractsWithAdminToast;

    /**
     * Each group's `_meta` carries its sidebar icon and description, plus the
     * permission needed to see it (`view`) and to save it (`manage`). A null
     * permission means every signed-in admin may.
     *
     * Field meta keys: label, type, placeholder, help, required, encrypted,
     * options, section, showWhen (['field' => 'value']).
     *
     * Kept public: the view reads `_meta` from it directly.
     */
    public const GROUP_DEFINITIONS = [
        'mail' => [
            '_meta' => [
                'icon' => 'mail',
                'description' => 'System & Touchpoint Email Account Designation Settings (cPanel SMTP transport configured in .env).',
                'view' => 'settings.view',
                'manage' => 'settings.manage',
            ],
            'from_name' => ['label' => 'System From Name', 'placeholder' => 'WestHub Healthcare', 'required' => true],
            'from_address' => ['label' => 'Default From Address', 'type' => 'email', 'placeholder' => 'no-reply@westhubhealthcare.com', 'required' => true],
            'appointments_from_address' => ['label' => 'Appointments Contact Email', 'type' => 'email', 'placeholder' => 'appointments@westhubhealthcare.com'],
            'applications_from_address' => ['label' => 'Careers & Applications Email', 'type' => 'email', 'placeholder' => 'careers@westhubhealthcare.com'],
            'enquiries_from_address' => ['label' => 'Enquiries & Contact Email', 'type' => 'email', 'placeholder' => 'info@westhubhealthcare.com'],
            'newsletter_from_address' => ['label' => 'Newsletter & Broadcast Email', 'type' => 'email', 'placeholder' => 'newsletter@westhubhealthcare.com'],
        ],
        'promotions' => [
            '_meta' => [
                'icon' => 'gift',
                'description' => 'The “1 Month Free Healthcare Services” popup. Changes go live on the website immediately.',
                'view' => 'promos.view',
                'manage' => 'promos.manage',
            ],
            'enabled' => [
                'label' => 'Show the promo popup on the website',
                'type' => 'boolean',
                'section' => 'Campaign',
                'help' => 'Turn this off to pause the promo immediately.',
            ],
            'starts_at' => ['label' => 'Starts on', 'type' => 'date', 'section' => 'Campaign', 'help' => 'Leave blank to start straight away.'],
            'ends_at' => ['label' => 'Ends on', 'type' => 'date', 'section' => 'Campaign', 'help' => 'After this date the popup stops showing on its own.'],
            'notify_emails' => [
                'label' => 'Send claim alerts to',
                'section' => 'Campaign',
                'placeholder' => 'care@westhubhealthcare.com, ops@westhubhealthcare.com',
                'help' => 'Comma separated. Falls back to the enquiries email, then the public contact email.',
            ],

            'eyebrow' => ['label' => 'Tag text', 'section' => 'Wording', 'placeholder' => 'Limited-time offer'],
            'offer_amount' => ['label' => 'Offer amount', 'section' => 'Wording', 'placeholder' => '1 Month'],
            'offer_highlight' => ['label' => 'Highlighted word', 'section' => 'Wording', 'placeholder' => 'FREE'],
            'offer_subline' => ['label' => 'Offer subline', 'type' => 'textarea', 'section' => 'Wording', 'placeholder' => 'of home care, nursing or therapeutic services for new clients'],
            'headline' => ['label' => 'Headline', 'type' => 'textarea', 'section' => 'Wording', 'placeholder' => 'Get your first month of healthcare services, free.'],
            'body' => ['label' => 'Body copy', 'type' => 'textarea', 'section' => 'Wording'],
            'cta_label' => ['label' => 'Button label', 'section' => 'Wording', 'placeholder' => 'Claim My Free Month'],
            'dismiss_label' => ['label' => 'Dismiss link', 'section' => 'Wording'],
            'included_services' => ['label' => 'Included services', 'section' => 'Wording', 'placeholder' => 'Home Care Services, Nursing Care, Therapeutic Services', 'help' => 'Comma separated. Shown as a checklist.'],
            'fine_print' => ['label' => 'Fine print', 'type' => 'textarea', 'section' => 'Wording'],

            'delay_seconds' => ['label' => 'Show after (seconds)', 'type' => 'number', 'section' => 'Behaviour', 'placeholder' => '4'],
            'scroll_percent' => ['label' => 'Or after scrolling (%)', 'type' => 'number', 'section' => 'Behaviour', 'placeholder' => '30'],
            'frequency_days' => ['label' => 'Do not re-show for (days)', 'type' => 'number', 'section' => 'Behaviour', 'placeholder' => '7'],
            'voucher_prefix' => ['label' => 'Voucher prefix', 'section' => 'Behaviour', 'placeholder' => 'WH-FREE30'],
            'voucher_validity_days' => ['label' => 'Voucher valid for (days)', 'type' => 'number', 'section' => 'Behaviour', 'placeholder' => '30'],
            'subscribe_on_consent' => ['label' => 'Add consenting claimants to the newsletter', 'type' => 'boolean', 'section' => 'Behaviour'],
        ],
        'appointments' => [
            '_meta' => [
                'icon' => 'calendar',
                'description' => 'Which booking system the website uses, and the account for each. Switching takes effect immediately.',
                'view' => 'settings.view',
                'manage' => 'settings.manage',
            ],
            'provider' => [
                'label' => 'Booking provider',
                'type' => 'select',
                'options' => ['calendly' => 'Calendly', 'google' => 'Google Calendar'],
                'section' => 'Provider',
                'help' => 'Which system the website booking form hands off to.',
            ],
            'timezone' => [
                'label' => 'Booking timezone',
                'section' => 'Provider',
                'placeholder' => 'America/Chicago',
                'help' => 'An IANA timezone name. Used for the times offered to visitors.',
            ],

            'calendly_url' => [
                'label' => 'Calendly Appointment Scheduling URL',
                'type' => 'url',
                'section' => 'Calendly',
                'placeholder' => 'https://calendly.com/westhub/appointment',
                'showWhen' => ['provider' => 'calendly'],
                'help' => 'Paste a different link here to switch Calendly accounts.',
            ],

            'google_calendar_id' => [
                'label' => 'Google Calendar ID',
                'section' => 'Google Calendar',
                'placeholder' => 'care-team@westhubhealthcare.com',
                'showWhen' => ['provider' => 'google'],
                'help' => 'The calendar bookings are written to. Share it with the service account below.',
            ],
            'google_service_account_email' => [
                'label' => 'Service account email',
                'type' => 'email',
                'section' => 'Google Calendar',
                'placeholder' => 'westhub-bookings@project.iam.gserviceaccount.com',
                'showWhen' => ['provider' => 'google'],
            ],
            'google_service_account_private_key' => [
                'label' => 'Service account key',
                'type' => 'textarea',
                'encrypted' => true,
                'section' => 'Google Calendar',
                'showWhen' => ['provider' => 'google'],
                'help' => 'Paste the whole JSON key file, or just the private key. Stored encrypted.',
            ],
            'google_event_duration_minutes' => ['label' => 'Appointment length (minutes)', 'type' => 'number', 'section' => 'Google Calendar', 'placeholder' => '60', 'showWhen' => ['provider' => 'google']],
            'google_meet_enabled' => ['label' => 'Add a Google Meet link to every booking', 'type' => 'boolean', 'section' => 'Google Calendar', 'showWhen' => ['provider' => 'google']],
            'google_business_days' => ['label' => 'Booking days', 'section' => 'Google Calendar', 'placeholder' => '1,2,3,4,5', 'help' => '1 = Monday through 7 = Sunday.', 'showWhen' => ['provider' => 'google']],
            'google_business_hours_start' => ['label' => 'First appointment', 'type' => 'time', 'section' => 'Google Calendar', 'placeholder' => '09:00', 'showWhen' => ['provider' => 'google']],
            'google_business_hours_end' => ['label' => 'Last appointment ends by', 'type' => 'time', 'section' => 'Google Calendar', 'placeholder' => '17:00', 'showWhen' => ['provider' => 'google']],
            'google_min_notice_hours' => ['label' => 'Minimum notice (hours)', 'type' => 'number', 'section' => 'Google Calendar', 'placeholder' => '24', 'showWhen' => ['provider' => 'google']],
            'google_booking_window_days' => ['label' => 'Bookable how far ahead (days)', 'type' => 'number', 'section' => 'Google Calendar', 'placeholder' => '30', 'showWhen' => ['provider' => 'google']],
        ],
        'integrations' => [
            '_meta' => [
                'icon' => 'table',
                'description' => 'Google Sheets sync for job applications and promo claims. Paste credentials here, no deploy needed.',
                'view' => 'settings.view',
                'manage' => 'settings.manage',
            ],
            'google_sheets_enabled' => [
                'label' => 'Sync submissions to Google Sheets',
                'type' => 'boolean',
                'section' => 'Google Sheets',
                'help' => 'When on, job applications and promo claims are appended to the spreadsheet as they arrive.',
            ],
            'google_sheets_spreadsheet_id' => [
                'label' => 'Spreadsheet ID',
                'section' => 'Google Sheets',
                'placeholder' => '1AbC...xyz',
                'help' => 'The long ID in the sheet URL between /d/ and /edit.',
            ],
            'google_sheets_service_account_email' => [
                'label' => 'Service account email',
                'type' => 'email',
                'section' => 'Google Sheets',
                'placeholder' => 'westhub-sheets@project.iam.gserviceaccount.com',
                'help' => 'Share the spreadsheet with this address and give it Editor access.',
            ],
            'google_sheets_private_key' => [
                'label' => 'Service account key',
                'type' => 'textarea',
                'encrypted' => true,
                'section' => 'Google Sheets',
                'help' => 'Paste the whole JSON key file, or just the private key. Stored encrypted.',
            ],
            'google_sheets_join_requests_tab' => ['label' => 'Job applications tab', 'section' => 'Google Sheets', 'placeholder' => 'Join Requests'],
            'google_sheets_promo_claims_tab' => ['label' => 'Promo claims tab', 'section' => 'Google Sheets', 'placeholder' => 'Promo Claims'],
        ],
        'contact' => [
            '_meta' => [
                'icon' => 'phone',
                'description' => 'Public contact information displayed across website headers, bars, and metadata.',
                'view' => 'settings.view',
                'manage' => 'settings.manage',
            ],
            'public_phone' => ['label' => 'Public Phone Number', 'type' => 'text', 'placeholder' => '+1 2246250423'],
            'public_email' => ['label' => 'Public Contact Email', 'type' => 'email', 'placeholder' => 'info@westhubhealthcare.com'],
        ],
        'password' => [
            '_meta' => [
                'icon' => 'lock',
                'description' => 'Update your personal account security and password credentials.',
                'view' => null,
                'manage' => null,
            ],
            'current_password' => ['label' => 'Current Password', 'type' => 'password', 'required' => true],
            'new_password' => ['label' => 'New Password', 'type' => 'password', 'required' => true],
            'new_password_confirmation' => ['label' => 'Confirm New Password', 'type' => 'password', 'required' => true],
        ],
    ];

    /** Which groups have a Test connection button, and what it tests. */
    private const TEST_TARGETS = [
        'integrations' => 'sheets',
        'appointments' => 'appointments',
    ];

    public bool $readyToLoad = true;
    public string $group = 'mail';
    public array $settings = [];
    public ?array $testResult = null;

    public function mount(): void
    {
        $this->group = $this->firstViewableGroup($this->group);
        $this->loadGroup();
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
        $this->group = $this->firstViewableGroup($this->group);
        $this->loadGroup();
    }

    public function setGroup(string $group): void
    {
        if (! array_key_exists($group, self::GROUP_DEFINITIONS) || ! $this->canView($group)) {
            return;
        }

        $this->group = $group;
        $this->testResult = null;
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

        // `group` is a public property the browser can set, so the permission
        // is checked here against whatever group is actually being saved.
        $this->authorizeManage($this->group);

        $validated = $this->validate($this->settingsRules());

        foreach ($this->visibleFields() as $key => $meta) {
            $isEncrypted = (bool) ($meta['encrypted'] ?? false);
            $newValue = $this->normalizeInputValue($validated['settings'][$key] ?? null, $meta);

            $existing = Setting::query()
                ->where('group', $this->group)
                ->where('key', $key)
                ->first();

            $oldValue = $existing ? $this->decodeStoredValue($existing) : null;

            // An empty encrypted field means "keep the saved secret", not "erase it".
            if ($isEncrypted && $newValue === null && $oldValue !== null) {
                continue;
            }

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

        SiteSettings::flush();

        $this->testResult = null;
        $this->toastSuccess('Settings saved. They are live on the website now.', 'Settings');
        $this->loadGroup();
    }

    /**
     * Prove credentials work before anyone relies on them.
     */
    public function testConnection(): void
    {
        $target = self::TEST_TARGETS[$this->group] ?? null;

        if ($target === null) {
            return;
        }

        $this->authorizeManage($this->group);

        SiteSettings::flush();

        try {
            $this->testResult = match ($target) {
                'sheets' => app(GoogleSheets::class)->testConnection(),
                'appointments' => app(AppointmentProviderManager::class)->testConnection(),
            };
        } catch (Throwable $e) {
            $this->testResult = ['ok' => false, 'message' => $e->getMessage()];
        }

        $this->testResult['ok']
            ? $this->toastSuccess($this->testResult['message'], 'Connected')
            : $this->toastError($this->testResult['message'], 'Not connected');
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

    /* -----------------------------------------------------------------
     | Permissions
     ----------------------------------------------------------------- */

    protected function groupMeta(string $group): array
    {
        return self::GROUP_DEFINITIONS[$group]['_meta'] ?? [];
    }

    /**
     * The permission a group requires for an action, or null when every admin
     * may. An explicit `null` in `_meta` means "open to all", so this must use
     * array_key_exists: `??` would treat that null as missing and fall back to
     * settings.*, which locked non-settings roles out of their own password.
     */
    protected function groupPermission(string $group, string $action): ?string
    {
        $meta = $this->groupMeta($group);

        return array_key_exists($action, $meta)
            ? $meta[$action]
            : ($action === 'view' ? 'settings.view' : 'settings.manage');
    }

    public function canView(string $group): bool
    {
        $permission = $this->groupPermission($group, 'view');

        return $permission === null || (bool) auth()->user()?->can($permission);
    }

    public function canManage(string $group): bool
    {
        $permission = $this->groupPermission($group, 'manage');

        return $permission === null || (bool) auth()->user()?->can($permission);
    }

    protected function authorizeManage(string $group): void
    {
        $permission = $this->groupPermission($group, 'manage');

        if ($permission !== null) {
            Gate::authorize($permission);
        }
    }

    protected function firstViewableGroup(string $preferred): string
    {
        if (array_key_exists($preferred, self::GROUP_DEFINITIONS) && $this->canView($preferred)) {
            return $preferred;
        }

        foreach (array_keys(self::GROUP_DEFINITIONS) as $group) {
            if ($this->canView($group)) {
                return $group;
            }
        }

        return 'password';
    }

    /* -----------------------------------------------------------------
     | Fields
     ----------------------------------------------------------------- */

    /**
     * Fields for the current group, minus `_meta` and anything hidden by a
     * showWhen rule.
     *
     * @return array<string, array<string, mixed>>
     */
    public function visibleFields(): array
    {
        $fields = self::GROUP_DEFINITIONS[$this->group] ?? [];
        unset($fields['_meta']);

        return array_filter($fields, function (array $meta): bool {
            foreach (($meta['showWhen'] ?? []) as $dependsOn => $expected) {
                $current = $this->settings[$dependsOn] ?? null;

                // An unset dependency falls back to its first option, so the
                // default provider's fields show on a fresh install.
                if ($current === null || $current === '') {
                    $options = self::GROUP_DEFINITIONS[$this->group][$dependsOn]['options'] ?? [];
                    $current = $options === [] ? null : array_key_first($options);
                }

                if ((string) $current !== (string) $expected) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    public function fieldsBySection(): array
    {
        $sections = [];

        foreach ($this->visibleFields() as $key => $meta) {
            $sections[$meta['section'] ?? ''][$key] = $meta;
        }

        return $sections;
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
            ->mapWithKeys(fn (Setting $setting): array => [$setting->key => $this->decodeStoredValue($setting) ?? ''])
            ->toArray();

        $fields = self::GROUP_DEFINITIONS[$this->group] ?? [];
        unset($fields['_meta']);

        $hydrated = [];

        foreach ($fields as $key => $meta) {
            $type = $meta['type'] ?? 'text';
            $value = $persisted[$key] ?? '';

            if ($type === 'select' && $value === '') {
                $value = (string) array_key_first($meta['options'] ?? ['' => '']);
            }

            // Livewire binds a checkbox with `checked = !!value`, and the string
            // "0" is truthy in JavaScript, so a switched-off toggle would render
            // ticked and be silently switched back on when the page is saved.
            if ($type === 'boolean') {
                $value = in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes', 'enabled'], true);
            }

            // Never send a stored secret back to the browser.
            if (($meta['encrypted'] ?? false) === true) {
                $value = '';
            }

            $hydrated[$key] = $value;
        }

        $this->settings = $hydrated;
    }

    protected function settingsRules(): array
    {
        $rules = [];

        foreach ($this->visibleFields() as $key => $meta) {
            $type = $meta['type'] ?? 'text';
            $required = ($meta['required'] ?? false) === true;

            $fieldRules = match ($type) {
                'boolean' => ['nullable', 'boolean'],
                'number' => ['nullable', 'numeric', 'min:0', 'max:100000'],
                'date' => ['nullable', 'date'],
                'time' => ['nullable', 'date_format:H:i'],
                'select' => ['nullable', 'string', 'in:' . implode(',', array_keys($meta['options'] ?? []))],
                'email' => [$required ? 'required' : 'nullable', 'string', 'max:5000', 'email:rfc'],
                'url' => [$required ? 'required' : 'nullable', 'string', 'max:5000', 'url'],
                default => [$required ? 'required' : 'nullable', 'string', 'max:20000'],
            };

            $rules["settings.$key"] = $fieldRules;
        }

        if ($this->group === 'appointments') {
            $rules['settings.timezone'] = ['nullable', 'string', 'timezone'];
        }

        return $rules;
    }

    protected function normalizeInputValue(mixed $value, array $meta = []): ?string
    {
        if (($meta['type'] ?? null) === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }

        $normalized = is_null($value) ? null : trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    protected function encodeValueForStorage(?string $value, bool $encrypted): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return $encrypted ? SettingsCrypto::encrypt($value) : $value;
    }

    protected function decodeStoredValue(Setting $setting): ?string
    {
        if (is_null($setting->value)) {
            return null;
        }

        if (! $setting->is_encrypted) {
            return (string) $setting->value;
        }

        return SettingsCrypto::decrypt((string) $setting->value);
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
        $groups = array_values(array_filter(
            array_keys(self::GROUP_DEFINITIONS),
            fn (string $group): bool => $this->canView($group)
        ));

        // The audit log reveals which settings changed and who changed them.
        $recentAudits = auth()->user()?->can('settings.view')
            ? SettingAudit::query()->with('actor', 'setting')->latest('changed_at')->take(12)->get()
            : collect();

        return view('livewire.admin.settings.index', [
            'groups' => $groups,
            'currentFields' => self::GROUP_DEFINITIONS[$this->group] ?? [],
            'sections' => $this->group === 'password' ? [] : $this->fieldsBySection(),
            'recentAudits' => $recentAudits,
            'sharedKeyConfigured' => SettingsCrypto::sharedKeyConfigured(),
            'testTarget' => self::TEST_TARGETS[$this->group] ?? null,
            'canManageGroup' => $this->canManage($this->group),
            'hasEncryptedFields' => collect(self::GROUP_DEFINITIONS[$this->group] ?? [])->contains(fn ($meta, $key) => $key !== '_meta' && ($meta['encrypted'] ?? false)),
        ])->layout('layouts.admin');
    }
}
