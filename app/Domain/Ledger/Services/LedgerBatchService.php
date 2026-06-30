<?php

namespace App\Domain\Ledger\Services;

use App\Domain\Ledger\Models\LedgerBatch;

class LedgerBatchService
{
    public function findPostedBatch(string $batchId): ?LedgerBatch
    {
        return LedgerBatch::query()
            ->with('entries.account')
            ->whereKey($batchId)
            ->where('status', \App\Domain\Ledger\Enums\LedgerEntryStatus::POSTED)
            ->first();
    }
}
