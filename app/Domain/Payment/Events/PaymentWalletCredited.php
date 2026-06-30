<?php

namespace App\Domain\Payment\Events;

use App\Domain\Payment\Models\PaymentTransaction;
use App\Domain\Wallet\Models\WalletTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentWalletCredited
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly PaymentTransaction $payment,
        public readonly WalletTransaction $walletTransaction,
    ) {
    }
}
