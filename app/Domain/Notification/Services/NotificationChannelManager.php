<?php

namespace App\Domain\Notification\Services;

use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Exceptions\InvalidNotificationChannelException;
use App\Domain\Notification\Interfaces\NotificationChannelAdapterInterface;
use App\Domain\Notification\Interfaces\NotificationChannelManagerInterface;
use App\Infrastructure\Notifications\Email\EmailChannelAdapter;

class NotificationChannelManager implements NotificationChannelManagerInterface
{
    public function __construct(private readonly EmailChannelAdapter $email)
    {
    }

    public function driver(NotificationChannel $channel): NotificationChannelAdapterInterface
    {
        return match ($channel) {
            NotificationChannel::EMAIL => $this->email,
            NotificationChannel::SMS,
            NotificationChannel::WHATSAPP,
            NotificationChannel::IN_APP,
            NotificationChannel::PUSH => throw new InvalidNotificationChannelException("Notification channel [{$channel->value}] is contracted but not implemented in Phase 7."),
        };
    }
}
