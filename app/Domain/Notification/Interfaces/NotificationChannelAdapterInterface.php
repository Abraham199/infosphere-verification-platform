<?php

namespace App\Domain\Notification\Interfaces;

use App\Domain\Notification\DTOs\NotificationDeliveryResult;
use App\Domain\Notification\Models\NotificationDelivery;

interface NotificationChannelAdapterInterface
{
    public function send(NotificationDelivery $delivery): NotificationDeliveryResult;
}
