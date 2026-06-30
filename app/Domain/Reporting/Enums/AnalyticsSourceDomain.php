<?php

namespace App\Domain\Reporting\Enums;

enum AnalyticsSourceDomain: string
{
    case WALLET = 'wallet';
    case LEDGER = 'ledger';
    case PAYMENT = 'payment';
    case VERIFICATION = 'verification';
    case PRODUCT = 'product';
    case NOTIFICATION = 'notification';
    case TENANT = 'tenant';
    case SYSTEM = 'system';
}
