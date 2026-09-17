<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Sensible defaults for the settings the site actually reads.
 *
 * Only writes keys that do not exist yet, so re-running a deploy never
 * overwrites what an operator has typed in the admin.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'appointments' => [
                'provider' => 'calendly',
                'timezone' => 'America/Chicago',
                'google_event_duration_minutes' => '60',
                'google_business_days' => '1,2,3,4,5',
                'google_business_hours_start' => '09:00',
                'google_business_hours_end' => '17:00',
                'google_min_notice_hours' => '24',
                'google_booking_window_days' => '30',
                'google_meet_enabled' => '0',
            ],
            'promotions' => [
                // Ships switched on so the campaign runs; flip to 0 in Settings
                // to pause it instantly.
                'enabled' => '1',
                'ends_at' => now()->addDays(90)->toDateString(),
                'eyebrow' => 'Limited-time offer',
                'offer_amount' => '1 Month',
                'offer_highlight' => 'FREE',
                'offer_subline' => 'of home care, nursing or therapeutic services for new clients',
                'headline' => 'Get your first month of healthcare services, free.',
                'body' => 'Book any home care, nursing or therapeutic service and your first month is on us. A care coordinator calls within one business day.',
                'cta_label' => 'Claim My Free Month',
                'dismiss_label' => "No thanks, I'll pass on the free month",
                'included_services' => 'Home Care Services, Nursing Care, Therapeutic Services',
                'fine_print' => 'New clients only. One redemption per household. Redeem within 30 days of issue. Full terms apply.',
                'delay_seconds' => '4',
                'scroll_percent' => '30',
                'frequency_days' => '7',
                'voucher_prefix' => 'WH-FREE30',
                'voucher_validity_days' => '30',
                'subscribe_on_consent' => '1',
            ],
            'integrations' => [
                'google_sheets_join_requests_tab' => 'Join Requests',
                'google_sheets_promo_claims_tab' => 'Promo Claims',
            ],
        ];

        foreach ($defaults as $group => $values) {
            foreach ($values as $key => $value) {
                Setting::query()->firstOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value, 'is_encrypted' => false, 'type' => 'string']
                );
            }
        }
    }
}
