<?php

namespace App\Domain\Notification\Enums;

enum NotificationTemplateStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case ARCHIVED = 'archived';
}
