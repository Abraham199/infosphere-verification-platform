<?php

namespace App\Domain\Notification\Events;

use App\Domain\Notification\Models\NotificationDelivery;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly NotificationDelivery $delivery)
    {
    }
}
