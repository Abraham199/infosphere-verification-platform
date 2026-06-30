<?php

namespace App\Domain\Ledger\Enums;

enum ReconciliationStatus: string
{
    case PENDING = 'pending';
    case MATCHED = 'matched';
    case DIFFERENCE_FOUND = 'difference_found';
    case FAILED = 'failed';
}
