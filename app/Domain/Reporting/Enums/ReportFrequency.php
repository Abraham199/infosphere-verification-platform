<?php

namespace App\Domain\Reporting\Enums;

enum ReportFrequency: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
}
