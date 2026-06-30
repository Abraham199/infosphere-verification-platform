<?php

namespace App\Contracts\Notifications;

interface NotificationChannelContract
{
    public function send(array $message): array;
}
