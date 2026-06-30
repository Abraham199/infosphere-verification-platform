<?php

namespace App\Domain\Ledger\Interfaces;

use App\Domain\Ledger\Models\ReconciliationRun;

interface LedgerReconciliationInterface
{
    public function reconcileWallet(string $tenantId, string $walletId): ReconciliationRun;
}
