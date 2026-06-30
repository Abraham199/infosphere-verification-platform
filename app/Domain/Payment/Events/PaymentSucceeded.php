<?php

namespace App\Domain\Payment\Events;

use App\Domain\Payment\Models\PaymentTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSucceeded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly PaymentTransaction $payment)
    {
    }
}
