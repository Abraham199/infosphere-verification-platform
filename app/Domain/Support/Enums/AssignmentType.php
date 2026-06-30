<?php

namespace App\Domain\Support\Enums;

enum AssignmentType: string
{
    case MANUAL = 'manual';
    case AUTO = 'auto';
    case TEAM = 'team';
    case ESCALATION = 'escalation';
}
