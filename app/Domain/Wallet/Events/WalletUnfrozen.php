<?php

namespace App\Domain\Wallet\Events;

use App\Domain\Wallet\Models\Wallet;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletUnfrozen
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Wallet $wallet)
    {
    }
}
