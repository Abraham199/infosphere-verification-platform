<?php

namespace App\Domain\Notification\Interfaces;

use App\Domain\Notification\DTOs\NotificationMessageData;
use App\Domain\Notification\Models\Notification;
use App\Domain\Notification\Models\NotificationDelivery;

interface NotificationServiceInterface
{
    public function queue(NotificationMessageData $message): Notification;
    public function deliver(NotificationDelivery $delivery): NotificationDelivery;
    public function retry(NotificationDelivery $delivery): NotificationDelivery;
}
