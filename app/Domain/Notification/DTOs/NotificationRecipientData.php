<?php

namespace App\Domain\Notification\DTOs;

use App\Domain\Notification\Enums\NotificationChannel;

class NotificationRecipientData
{
    public function __construct(
        public readonly NotificationChannel $channel,
        public readonly string $address,
    ) {
    }
}
