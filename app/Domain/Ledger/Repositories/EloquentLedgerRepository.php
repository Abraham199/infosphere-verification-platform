<?php

namespace App\Domain\Ledger\Repositories;

use App\Domain\Ledger\Interfaces\LedgerRepositoryInterface;
use App\Domain\Ledger\Models\LedgerAccount;
use App\Domain\Ledger\Models\LedgerBatch;
use App\Domain\Ledger\Models\LedgerEntry;

class EloquentLedgerRepository implements LedgerRepositoryInterface
{
    public function batchReferenceExists(string $reference): bool
    {
        return LedgerBatch::query()->where('reference', $reference)->exists();
    }

    public function entryReferenceExists(string $reference): bool
    {
        return LedgerEntry::query()->where('entry_reference', $reference)->exists();
    }

    public function findAccount(string $accountId): ?LedgerAccount
    {
        return LedgerAccount::query()->whereKey($accountId)->first();
    }

    public function findTenantAccount(?string $tenantId, string $code, string $currency): ?LedgerAccount
    {
        return LedgerAccount::query()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->where('currency', strtoupper($currency))
            ->first();
    }

    public function findBatch(string $batchId): ?LedgerBatch
    {
        return LedgerBatch::query()->whereKey($batchId)->first();
    }
}
