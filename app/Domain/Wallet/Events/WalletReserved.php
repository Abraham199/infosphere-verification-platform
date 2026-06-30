<?php

namespace App\Domain\Wallet\Events;

use App\Domain\Wallet\Models\WalletReservation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletReserved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly WalletReservation $reservation)
    {
    }
}
