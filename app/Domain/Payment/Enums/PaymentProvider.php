<?php

namespace App\Domain\Payment\Enums;

enum PaymentProvider: string
{
    case PAYSTACK = 'paystack';
}
