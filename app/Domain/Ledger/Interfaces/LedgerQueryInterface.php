<?php

namespace App\Domain\Ledger\Interfaces;

use App\Domain\Wallet\ValueObjects\Money;

interface LedgerQueryInterface
{
    public function accountBalance(string $accountId): Money;

    public function tenantAccountBalance(?string $tenantId, string $accountCode, string $currency): Money;
}
