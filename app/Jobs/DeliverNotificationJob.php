<?php

namespace App\Jobs;

use App\Domain\Notification\Interfaces\NotificationServiceInterface;
use App\Domain\Notification\Models\NotificationDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeliverNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly NotificationDelivery $delivery)
    {
    }

    public function handle(NotificationServiceInterface $notifications): void
    {
        $notifications->deliver($this->delivery);
    }
}
