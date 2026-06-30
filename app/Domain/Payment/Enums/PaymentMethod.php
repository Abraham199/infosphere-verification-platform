<?php

namespace App\Domain\Payment\Enums;

enum PaymentMethod: string
{
    case CARD = 'card';
    case BANK = 'bank';
    case TRANSFER = 'transfer';
    case USSD = 'ussd';
    case QR = 'qr';
    case MOBILE_MONEY = 'mobile_money';
    case UNKNOWN = 'unknown';
}
