<?php

namespace App\Domain\Notification\Enums;

enum NotificationStatus: string
{
    case QUEUED = 'queued';
    case SENT = 'sent';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case READ = 'read';
}
