<?php

namespace App\Domain\Developer\Enums;

enum WebhookStatus: string
{
    case ACTIVE = 'active';
    case DISABLED = 'disabled';
    case FAILED = 'failed';
}
