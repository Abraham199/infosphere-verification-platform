<?php

namespace App\Domain\Developer\Events;

use App\Domain\Developer\Models\WebhookDelivery;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebhookFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly WebhookDelivery $delivery)
    {
    }
}
