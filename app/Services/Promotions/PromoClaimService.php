<?php

namespace App\Services\Promotions;

use App\Jobs\Promotions\AppendPromoClaimToGoogleSheet;
use App\Mail\PromoClaimInternalAlert;
use App\Mail\PromoVoucherIssued;
use App\Models\PromoClaim;
use App\Models\Subscriber;
use App\Support\PromoOffer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Turns a promo popup submission into a stored claim, a voucher, and the
 * notifications that follow it.
 *
 * Side effects (email, Sheets, newsletter) are deliberately non-fatal: a
 * visitor must never see an error because an integration is misconfigured.
 */
class PromoClaimService
{
    /**
     * Returns an existing live claim for this email so a repeat visitor is
     * shown their original voucher instead of being handed a second one.
     */
    public function existingClaimFor(string $email): ?PromoClaim
    {
        return PromoClaim::query()
            ->where('campaign', PromoOffer::CAMPAIGN)
            ->where('email', mb_strtolower(trim($email)))
            ->whereIn('status', [PromoClaim::STATUS_NEW, PromoClaim::STATUS_CONTACTED, PromoClaim::STATUS_REDEEMED])
            ->latest('id')
            ->first();
    }

    /**
     * @param  array{full_name: string, email: string, phone?: ?string, service_id?: ?int, consent: bool, source_page?: ?string, meta?: array<string, mixed>}  $data
     */
    public function claim(array $data, PromoOffer $offer): PromoClaim
    {
        $email = mb_strtolower(trim($data['email']));

        $existing = $this->existingClaimFor($email);

        if ($existing) {
            return $existing;
        }

        $claim = PromoClaim::create([
            'campaign' => PromoOffer::CAMPAIGN,
            'full_name' => trim($data['full_name']),
            'email' => $email,
            'phone' => $data['phone'] ?? null,
            'service_id' => $data['service_id'] ?? null,
            'voucher_code' => $this->generateVoucherCode($offer),
            'status' => PromoClaim::STATUS_NEW,
            'consent_at' => ($data['consent'] ?? false) ? now() : null,
            'source_page' => $data['source_page'] ?? null,
            'expires_at' => $offer->voucherExpiresAt(),
            'meta' => $data['meta'] ?? [],
        ]);

        $this->notifyVisitor($claim, $offer);
        $this->notifyTeam($claim, $offer);
        $this->syncToSheet($claim);
        $this->subscribeIfConsented($claim, $offer);

        return $claim;
    }

    /**
     * Voucher codes are short enough to read out over the phone and are
     * uniqueness-checked against the table rather than trusted to entropy.
     */
    public function generateVoucherCode(PromoOffer $offer): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no 0/O/1/I

        for ($attempt = 0; $attempt < 12; $attempt++) {
            $suffix = '';

            for ($i = 0; $i < 5; $i++) {
                $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }

            $code = $offer->voucherPrefix . '-' . $suffix;

            if (! PromoClaim::query()->where('voucher_code', $code)->exists()) {
                return $code;
            }
        }

        return $offer->voucherPrefix . '-' . strtoupper(Str::random(10));
    }

    /**
     * Find a redeemable claim by code, for the booking form.
     */
    public function findRedeemable(?string $code): ?PromoClaim
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '') {
            return null;
        }

        $claim = PromoClaim::query()->where('voucher_code', $code)->first();

        return $claim && $claim->isRedeemable() ? $claim : null;
    }

    /**
     * Link a claim to the appointment that redeemed it.
     */
    public function markRedeemed(PromoClaim $claim, ?int $appointmentId = null): void
    {
        if ($claim->status === PromoClaim::STATUS_REDEEMED) {
            return;
        }

        $claim->forceFill([
            'status' => PromoClaim::STATUS_REDEEMED,
            'redeemed_at' => now(),
            'appointment_id' => $appointmentId ?? $claim->appointment_id,
        ])->save();

        $this->updateSheetStatus($claim);
    }

    /**
     * Flip claims whose voucher window has closed. Safe to run repeatedly.
     */
    public function expireStaleClaims(): int
    {
        return PromoClaim::query()
            ->whereIn('status', [PromoClaim::STATUS_NEW, PromoClaim::STATUS_CONTACTED])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update([
                'status' => PromoClaim::STATUS_EXPIRED,
                'updated_at' => now(),
            ]);
    }

    public function notifyVisitor(PromoClaim $claim, PromoOffer $offer): void
    {
        try {
            Mail::to($claim->email)->send(new PromoVoucherIssued($claim, $offer));

            $claim->forceFill(['emailed_at' => now()])->save();
        } catch (Throwable $e) {
            Log::warning('Promo voucher email failed for claim ' . $claim->id . ': ' . $e->getMessage());
        }
    }

    public function notifyTeam(PromoClaim $claim, PromoOffer $offer): void
    {
        $recipients = $offer->resolvedNotifyEmails();

        if ($recipients === []) {
            return;
        }

        try {
            Mail::to($recipients)->send(new PromoClaimInternalAlert($claim, $offer));
        } catch (Throwable $e) {
            Log::warning('Promo internal alert failed for claim ' . $claim->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Write the row now, so the spreadsheet is current even where no queue
     * worker is running. A slow or failing Google hands over to the queue,
     * which retries with backoff; the claim itself is already saved either
     * way, and the queued copy checks for the row before appending.
     */
    public function syncToSheet(PromoClaim $claim): void
    {
        if ($this->trySyncNow($claim->id, statusOnly: false)) {
            return;
        }

        try {
            AppendPromoClaimToGoogleSheet::dispatch($claim->id);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /** True when the immediate attempt succeeded. */
    protected function trySyncNow(int $claimId, bool $statusOnly): bool
    {
        try {
            AppendPromoClaimToGoogleSheet::dispatchSync($claimId, $statusOnly, true);

            return true;
        } catch (Throwable $e) {
            Log::warning('Promo claim sheet sync deferred to the queue.', [
                'promo_claim_id' => $claimId,
                'status_only' => $statusOnly,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function updateSheetStatus(PromoClaim $claim): void
    {
        if ($this->trySyncNow($claim->id, statusOnly: true)) {
            return;
        }

        try {
            AppendPromoClaimToGoogleSheet::dispatch($claim->id, true);
        } catch (Throwable $e) {
            report($e);
        }
    }

    protected function subscribeIfConsented(PromoClaim $claim, PromoOffer $offer): void
    {
        if (! $offer->subscribeOnConsent || ! $claim->consent_at) {
            return;
        }

        try {
            DB::connection((new Subscriber)->getConnectionName())->transaction(function () use ($claim): void {
                Subscriber::updateOrCreate(
                    ['email' => $claim->email],
                    [
                        'full_name' => $claim->full_name,
                        'source' => 'promo_' . PromoOffer::CAMPAIGN,
                        'status' => Subscriber::STATUS_SUBSCRIBED,
                        'subscribed_at' => now(),
                        'unsubscribed_at' => null,
                    ]
                );
            });
        } catch (Throwable $e) {
            Log::warning('Promo newsletter opt-in failed for claim ' . $claim->id . ': ' . $e->getMessage());
        }
    }
}
