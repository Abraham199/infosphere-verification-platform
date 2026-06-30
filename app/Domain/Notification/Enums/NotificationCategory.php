<?php

namespace App\Domain\Notification\Enums;

enum NotificationCategory: string
{
    case FINANCIAL = 'financial';
    case SECURITY = 'security';
    case SERVICE = 'service';
    case MARKETING = 'marketing';
    case SYSTEM = 'system';

    public function isMandatory(): bool
    {
        return in_array($this, [self::FINANCIAL, self::SECURITY], true);
    }
}
