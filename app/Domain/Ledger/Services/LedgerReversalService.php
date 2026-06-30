<?php

namespace App\Domain\Ledger\Services;

use App\Domain\Ledger\Enums\LedgerEntryType;
use App\Domain\Ledger\Events\LedgerEntryReversed;
use App\Domain\Ledger\Models\LedgerBatch;
use App\Domain\Ledger\Validators\LedgerValidationService;
use App\Domain\Ledger\ValueObjects\JournalEntry;
use App\Domain\Ledger\ValueObjects\LedgerLine;
use App\Domain\Wallet\ValueObjects\Currency;
use App\Domain\Wallet\ValueObjects\Money;
use Illuminate\Support\Str;

class LedgerReversalService
{
    public function __construct(
        private readonly LedgerValidationService $validator,
        private readonly LedgerPostingService $posting,
    ) {
    }

    public function reverse(LedgerBatch $batch, string $reason): LedgerBatch
    {
        $batch->load('entries');
        $this->validator->assertCanReverse($batch);

        $lines = $batch->entries->map(function ($entry): LedgerLine {
            $type = $entry->type === LedgerEntryType::DEBIT ? LedgerEntryType::CREDIT : LedgerEntryType::DEBIT;

            return new LedgerLine(
                accountId: $entry->ledger_account_id,
                type: $type,
                amount: Money::fromDecimal($entry->amount, $entry->currency),
                metadata: ['reversal_of_entry_id' => $entry->id],
            );
        })->all();

        $reversal = $this->posting->post(new JournalEntry(
            tenantId: $batch->tenant_id,
            reference: 'REV-'.$batch->reference.'-'.strtoupper((string) Str::ulid()),
            description: 'Reversal: '.$reason,
            currency: new Currency($batch->currency),
            lines: $lines,
            metadata: ['reversal_of_batch_id' => $batch->id, 'reason' => $reason],
            sourceType: $batch::class,
            sourceId: $batch->id,
        ));

        $reversal->forceFill(['reversal_of_batch_id' => $batch->id])->save();
        LedgerEntryReversed::dispatch($reversal);

        return $reversal->refresh();
    }
}
