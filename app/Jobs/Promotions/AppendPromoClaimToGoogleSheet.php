<?php

namespace App\Jobs\Promotions;

use App\Models\PromoClaim;
use App\Services\Google\GoogleSheets;
use App\Support\Sheets\PromoClaimSheet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Writes a promo claim to the Google Sheet, or updates its status columns when
 * $statusOnly is set.
 *
 * The job no-ops silently when Sheets is not configured, so the promo works
 * fine before anyone connects a spreadsheet, and starts syncing the moment
 * someone does.
 */
class AppendPromoClaimToGoogleSheet implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    public function __construct(
        public int $claimId,
        public bool $statusOnly = false,
    ) {
    }

    public function handle(GoogleSheets $sheets): void
    {
        if (! $sheets->isEnabled()) {
            return;
        }

        $claim = PromoClaim::query()->with('service')->find($this->claimId);

        if (! $claim) {
            return;
        }

        $tab = $sheets->tab(PromoClaimSheet::TAB_KEY, PromoClaimSheet::TAB_DEFAULT);

        try {
            if ($this->statusOnly) {
                $updated = 0;

                foreach (PromoClaimSheet::statusRow($claim) as $header => $value) {
                    if ($sheets->updateRowColumn($tab, PromoClaimSheet::HEADERS, (string) $claim->id, $header, $value)) {
                        $updated++;
                    }
                }

                if ($updated === 0) {
                    // The row was never written (Sheets connected after the claim
                    // was made), so write it in full now.
                    $sheets->appendRow($tab, PromoClaimSheet::HEADERS, PromoClaimSheet::row($claim));
                }
            } else {
                $sheets->appendRow($tab, PromoClaimSheet::HEADERS, PromoClaimSheet::row($claim));
            }

            $claim->forceFill(['synced_at' => now()])->save();
        } catch (Throwable $e) {
            Log::error('Promo claim Google Sheets sync failed.', [
                'promo_claim_id' => $claim->id,
                'status_only' => $this->statusOnly,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
