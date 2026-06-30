<?php

namespace App\Domain\Wallet\Enums;

enum TransactionType: string
{
    case CREDIT = 'credit';
    case DEBIT = 'debit';
    case RESERVATION = 'reservation';
    case RELEASE = 'release';
    case REFUND = 'refund';
    case ADJUSTMENT = 'adjustment';
}
