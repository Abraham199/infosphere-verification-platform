<?php

namespace App\Domain\Ledger\Interfaces;

use App\Domain\Ledger\Models\LedgerBatch;
use App\Domain\Ledger\ValueObjects\JournalEntry;

interface LedgerPostingInterface
{
    public function post(JournalEntry $journalEntry): LedgerBatch;
}
