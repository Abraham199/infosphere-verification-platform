<?php

namespace App\Domain\Ledger\Services;

use App\Domain\Ledger\Enums\LedgerEntryStatus;
use App\Domain\Ledger\Models\LedgerBatch;
use App\Domain\Ledger\Models\LedgerEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LedgerQueryService
{
    public function entriesForAccount(string $accountId, int $perPage = 50): LengthAwarePaginator
    {
        return LedgerEntry::query()
            ->with('batch')
            ->where('ledger_account_id', $accountId)
            ->where('status', LedgerEntryStatus::POSTED)
            ->latest('posted_at')
            ->paginate($perPage);
    }

    public function batchesForTenant(?string $tenantId, int $perPage = 50): LengthAwarePaginator
    {
        return LedgerBatch::query()
            ->with('entries')
            ->where('tenant_id', $tenantId)
            ->latest('posted_at')
            ->paginate($perPage);
    }
}
