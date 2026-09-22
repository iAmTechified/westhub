<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * The "2 Weeks Free Healthcare Services" offer, assembled from the shared
 * settings table.
 *
 * Everything an operator is likely to change (copy, dates, timing, voucher
 * rules) lives in settings, so running or pausing the promo never needs a
 * deploy. The defaults here are what ships if nobody ever opens the admin.
 */
class PromoOffer
{
    public const CAMPAIGN = 'free_month';
    public const GROUP = 'promotions';

    public function __construct(
        public readonly bool $enabled,
        public readonly string $eyebrow,
        public readonly string $offerAmount,
        public readonly string $offerHighlight,
        public readonly string $offerSubline,
        public readonly string $headline,
        public readonly string $body,
        public readonly string $ctaLabel,
        public readonly string $dismissLabel,
        public readonly string $finePrint,
        public readonly ?Carbon $startsAt,
        public readonly ?Carbon $endsAt,
        public readonly int $delaySeconds,
        public readonly int $scrollPercent,
        public readonly int $frequencyDays,
        public readonly int $voucherValidityDays,
        public readonly string $voucherPrefix,
        public readonly bool $subscribeOnConsent,
        /** @var array<int, string> */
        public readonly array $includedServices,
        /** @var array<int, string> */
        public readonly array $notifyEmails,
    ) {
    }

    public static function fromSettings(): self
    {
        $g = self::GROUP;

        return new self(
            enabled: SiteSettings::bool($g, 'enabled', false),
            eyebrow: (string) SiteSettings::get($g, 'eyebrow', 'Limited-time offer'),
            offerAmount: (string) SiteSettings::get($g, 'offer_amount', '2 Weeks'),
            offerHighlight: (string) SiteSettings::get($g, 'offer_highlight', 'FREE'),
            offerSubline: (string) SiteSettings::get($g, 'offer_subline', 'of home care, nursing or therapeutic services for new clients'),
            headline: (string) SiteSettings::get($g, 'headline', 'Get your first two weeks of healthcare services, free.'),
            body: (string) SiteSettings::get($g, 'body', 'Book any home care, nursing or therapeutic service and your first two weeks are on us. A care coordinator calls within one business day.'),
            ctaLabel: (string) SiteSettings::get($g, 'cta_label', 'Claim My Free 2 Weeks'),
            dismissLabel: (string) SiteSettings::get($g, 'dismiss_label', "No thanks, I'll pass on the free two weeks"),
            finePrint: (string) SiteSettings::get($g, 'fine_print', 'New clients only. One redemption per household. Full terms apply.'),
            startsAt: SiteSettings::date($g, 'starts_at'),
            endsAt: SiteSettings::date($g, 'ends_at')?->endOfDay(),
            delaySeconds: max(0, SiteSettings::int($g, 'delay_seconds', 4)),
            scrollPercent: min(100, max(0, SiteSettings::int($g, 'scroll_percent', 30))),
            frequencyDays: max(0, SiteSettings::int($g, 'frequency_days', 7)),
            voucherValidityDays: max(1, SiteSettings::int($g, 'voucher_validity_days', 30)),
            voucherPrefix: strtoupper(trim((string) SiteSettings::get($g, 'voucher_prefix', 'WH-FREE14'))) ?: 'WH-FREE14',
            subscribeOnConsent: SiteSettings::bool($g, 'subscribe_on_consent', true),
            includedServices: self::splitList((string) SiteSettings::get($g, 'included_services', 'Home Care Services, Nursing Care, Therapeutic Services')),
            notifyEmails: self::splitEmails((string) SiteSettings::get($g, 'notify_emails', '')),
        );
    }

    /**
     * True when the promo should be served to visitors right now.
     */
    public function isLive(?Carbon $now = null): bool
    {
        if (! $this->enabled) {
            return false;
        }

        $now ??= Carbon::now();

        if ($this->startsAt && $now->lessThan($this->startsAt)) {
            return false;
        }

        if ($this->endsAt && $now->greaterThan($this->endsAt)) {
            return false;
        }

        return true;
    }

    public function endsAtLabel(): ?string
    {
        return $this->endsAt?->format('j M Y');
    }

    public function voucherExpiresAt(?Carbon $from = null): Carbon
    {
        $expiry = ($from ?? Carbon::now())->copy()->addDays($this->voucherValidityDays)->endOfDay();

        // A voucher should never outlive the campaign by more than its validity
        // window measured from the campaign end.
        if ($this->endsAt) {
            $campaignCap = $this->endsAt->copy()->addDays($this->voucherValidityDays)->endOfDay();

            if ($expiry->greaterThan($campaignCap)) {
                return $campaignCap;
            }
        }

        return $expiry;
    }

    /** @return array<int, string> */
    protected static function splitList(string $raw): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    /** @return array<int, string> */
    protected static function splitEmails(string $raw): array
    {
        $emails = array_map('trim', preg_split('/[,;\s]+/', $raw) ?: []);

        return array_values(array_unique(array_filter(
            $emails,
            static fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false
        )));
    }

    /**
     * Where internal alerts go: the promo-specific list, else the enquiries
     * inbox, else the appointments inbox, else the public contact address.
     *
     * @return array<int, string>
     */
    public function resolvedNotifyEmails(): array
    {
        if ($this->notifyEmails !== []) {
            return $this->notifyEmails;
        }

        foreach ([
            SiteSettings::enquiriesEmail(),
            SiteSettings::appointmentsEmail(),
            SiteSettings::contactEmail(),
        ] as $candidate) {
            if (filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                return [$candidate];
            }
        }

        return [];
    }
}
