<?php

namespace App\Domain\Notification\Enums;

enum NotificationChannel: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
    case WHATSAPP = 'whatsapp';
    case IN_APP = 'in_app';
    case PUSH = 'push';
}
