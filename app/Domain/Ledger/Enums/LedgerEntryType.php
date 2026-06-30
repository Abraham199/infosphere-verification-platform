<?php

namespace App\Domain\Ledger\Enums;

enum LedgerEntryType: string
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';
}
