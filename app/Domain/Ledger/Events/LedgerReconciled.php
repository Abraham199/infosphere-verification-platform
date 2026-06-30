<?php

namespace App\Domain\Ledger\Events;

use App\Domain\Ledger\Models\ReconciliationRun;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LedgerReconciled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ReconciliationRun $run)
    {
    }
}
