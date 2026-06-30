<?php

namespace App\Domain\Ledger\Enums;

enum LedgerEntryStatus: string
{
    case PENDING = 'pending';
    case POSTED = 'posted';
    case REVERSED = 'reversed';
}
