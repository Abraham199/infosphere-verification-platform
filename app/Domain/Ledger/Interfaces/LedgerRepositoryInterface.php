<?php

namespace App\Domain\Ledger\Interfaces;

use App\Domain\Ledger\Models\LedgerAccount;
use App\Domain\Ledger\Models\LedgerBatch;

interface LedgerRepositoryInterface
{
    public function batchReferenceExists(string $reference): bool;

    public function entryReferenceExists(string $reference): bool;

    public function findAccount(string $accountId): ?LedgerAccount;

    public function findTenantAccount(?string $tenantId, string $code, string $currency): ?LedgerAccount;

    public function findBatch(string $batchId): ?LedgerBatch;
}
