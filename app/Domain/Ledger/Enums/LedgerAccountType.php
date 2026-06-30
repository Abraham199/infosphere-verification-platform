<?php

namespace App\Domain\Ledger\Enums;

enum LedgerAccountType: string
{
    case ASSET = 'asset';
    case LIABILITY = 'liability';
    case REVENUE = 'revenue';
    case EXPENSE = 'expense';
    case EQUITY = 'equity';
}
