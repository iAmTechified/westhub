<?php

namespace App\Console\Commands;

use App\Services\Promotions\PromoClaimService;
use Illuminate\Console\Command;

class ExpirePromoClaims extends Command
{
    protected $signature = 'westhub:expire-promo-claims';

    protected $description = 'Mark promo vouchers whose redemption window has closed as expired';

    public function handle(PromoClaimService $claims): int
    {
        $expired = $claims->expireStaleClaims();

        $this->info($expired === 0
            ? 'No promo vouchers needed expiring.'
            : "Expired {$expired} promo voucher(s).");

        return self::SUCCESS;
    }
}
