<?php

namespace App\Domain\Reporting\Enums;

enum MetricCategory: string
{
    case FINANCIAL = 'financial';
    case VERIFICATION = 'verification';
    case PAYMENT = 'payment';
    case PRODUCT = 'product';
    case TENANT = 'tenant';
    case SYSTEM = 'system';
}
