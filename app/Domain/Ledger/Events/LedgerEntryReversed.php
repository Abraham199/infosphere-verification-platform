<?php

namespace App\Domain\Ledger\Events;

use App\Domain\Ledger\Models\LedgerBatch;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LedgerEntryReversed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly LedgerBatch $reversalBatch)
    {
    }
}
