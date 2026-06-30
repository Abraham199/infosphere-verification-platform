<?php

namespace App\Domain\Notification\Interfaces;

use App\Domain\Notification\Enums\NotificationChannel;

interface NotificationChannelManagerInterface
{
    public function driver(NotificationChannel $channel): NotificationChannelAdapterInterface;
}
