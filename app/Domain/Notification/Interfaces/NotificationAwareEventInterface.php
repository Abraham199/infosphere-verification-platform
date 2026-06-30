<?php

namespace App\Domain\Notification\Interfaces;

use App\Domain\Notification\DTOs\NotificationMessageData;

interface NotificationAwareEventInterface
{
    public function toNotificationMessage(): NotificationMessageData;
}
