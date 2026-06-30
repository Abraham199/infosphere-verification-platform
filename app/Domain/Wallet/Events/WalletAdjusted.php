<?php

namespace App\Domain\Wallet\Events;

use App\Domain\Wallet\Models\WalletAdjustment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletAdjusted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly WalletAdjustment $adjustment)
    {
    }
}
