<?php

namespace App\Listeners;

use App\Domain\Notification\Interfaces\NotificationAwareEventInterface;
use App\Domain\Notification\Interfaces\NotificationServiceInterface;

class DispatchDomainNotification
{
    public function __construct(private readonly NotificationServiceInterface $notifications)
    {
    }

    public function handle(NotificationAwareEventInterface $event): void
    {
        $this->notifications->queue($event->toNotificationMessage());
    }
}
