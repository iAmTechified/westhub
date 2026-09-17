<?php

namespace App\Support\Sheets;

use App\Models\PromoClaim;

/**
 * The column contract for the Promo Claims tab.
 *
 * Columns are matched by NAME, so an operator can reorder them in the
 * spreadsheet and add their own columns without breaking the sync. `owner` and
 * `notes` are left for humans and are never written by WestHub.
 */
class PromoClaimSheet
{
    public const TAB_KEY = 'promo_claims';
    public const TAB_DEFAULT = 'Promo Claims';

    public const HEADERS = [
        'westhub_id',
        'claimed_at',
        'campaign',
        'full_name',
        'email',
        'phone',
        'service',
        'voucher_code',
        'status',
        'expires_at',
        'redeemed_at',
        'appointment_id',
        'source_page',
        'marketing_consent',
        'owner',
        'notes',
        'admin_url',
    ];

    /** Columns WestHub keeps up to date after the row is first written. */
    public const SYNCED_COLUMNS = ['status', 'redeemed_at', 'appointment_id'];

    /**
     * @return array<string, string>
     */
    public static function row(PromoClaim $claim): array
    {
        return [
            'westhub_id' => (string) $claim->id,
            'claimed_at' => optional($claim->created_at)->toDateTimeString() ?? '',
            'campaign' => (string) $claim->campaign,
            'full_name' => (string) $claim->full_name,
            'email' => (string) $claim->email,
            'phone' => (string) ($claim->phone ?? ''),
            'service' => (string) ($claim->service?->name ?? ''),
            'voucher_code' => (string) $claim->voucher_code,
            'status' => (string) $claim->status,
            'expires_at' => optional($claim->expires_at)->toDateString() ?? '',
            'redeemed_at' => optional($claim->redeemed_at)->toDateTimeString() ?? '',
            'appointment_id' => (string) ($claim->appointment_id ?? ''),
            'source_page' => (string) ($claim->source_page ?? ''),
            'marketing_consent' => $claim->consent_at ? 'yes' : 'no',
            'admin_url' => rtrim((string) config('services.westhub_admin.base_url'), '/') . '/admin/promo-claims',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusRow(PromoClaim $claim): array
    {
        return [
            'status' => (string) $claim->status,
            'redeemed_at' => optional($claim->redeemed_at)->toDateTimeString() ?? '',
            'appointment_id' => (string) ($claim->appointment_id ?? ''),
        ];
    }
}
