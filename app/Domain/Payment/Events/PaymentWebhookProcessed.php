<?php

namespace App\Domain\Payment\Events;

use App\Domain\Payment\Models\PaymentWebhook;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentWebhookProcessed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly PaymentWebhook $webhook)
    {
    }
}
