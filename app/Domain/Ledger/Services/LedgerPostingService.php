<?php

namespace App\Domain\Ledger\Services;

use App\Domain\Ledger\Enums\LedgerEntryStatus;
use App\Domain\Ledger\Enums\LedgerAccountType;
use App\Domain\Ledger\Enums\LedgerEntryType;
use App\Domain\Ledger\Events\LedgerBatchCompleted;
use App\Domain\Ledger\Events\LedgerEntryPosted;
use App\Domain\Ledger\Interfaces\LedgerPostingInterface;
use App\Domain\Ledger\Models\LedgerBatch;
use App\Domain\Ledger\Models\LedgerAccount;
use App\Domain\Ledger\Models\LedgerEntry;
use App\Domain\Ledger\Validators\LedgerValidationService;
use App\Domain\Ledger\ValueObjects\JournalEntry;
use App\Services\BaseService;
use Illuminate\Support\Str;

class LedgerPostingService extends BaseService implements LedgerPostingInterface
{
    public function __construct(private readonly LedgerValidationService $validator)
    {
    }

    public function post(JournalEntry $journalEntry): LedgerBatch
    {
        return $this->transaction(function () use ($journalEntry): LedgerBatch {
            $this->validator->assertCanPost($journalEntry);

            $totalDebits = 0;
            $totalCredits = 0;

            foreach ($journalEntry->lines as $line) {
                if ($line->type === LedgerEntryType::DEBIT) {
                    $totalDebits += $line->amount->minorUnits;
                } else {
                    $totalCredits += $line->amount->minorUnits;
                }
            }

            $batch = LedgerBatch::query()->create([
                'tenant_id' => $journalEntry->tenantId,
                'reference' => $journalEntry->reference,
                'description' => $journalEntry->description,
                'currency' => $journalEntry->currency->value(),
                'total_debits' => number_format($totalDebits / 100, 2, '.', ''),
                'total_credits' => number_format($totalCredits / 100, 2, '.', ''),
                'status' => LedgerEntryStatus::POSTED,
                'source_type' => $journalEntry->sourceType,
                'source_id' => $journalEntry->sourceId,
                'posted_at' => now(),
                'metadata' => $journalEntry->metadata,
            ]);

            foreach ($journalEntry->lines as $index => $line) {
                $account = LedgerAccount::query()->findOrFail($line->accountId);
                $currentBalance = app(LedgerBalanceService::class)->accountBalance($line->accountId);
                $normalDebit = in_array($account->type, [LedgerAccountType::ASSET, LedgerAccountType::EXPENSE], true);
                $increasesBalance = ($normalDebit && $line->type === LedgerEntryType::DEBIT)
                    || (! $normalDebit && $line->type === LedgerEntryType::CREDIT);
                $balanceAfter = $increasesBalance
                    ? $currentBalance->add($line->amount)
                    : new \App\Domain\Wallet\ValueObjects\Money(
                        max(0, $currentBalance->minorUnits - $line->amount->minorUnits),
                        $currentBalance->currency
                    );

                LedgerEntry::query()->create([
                    'tenant_id' => $journalEntry->tenantId,
                    'ledger_batch_id' => $batch->id,
                    'ledger_account_id' => $line->accountId,
                    'entry_reference' => $journalEntry->reference.'-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT).'-'.strtoupper((string) Str::ulid()),
                    'type' => $line->type,
                    'status' => LedgerEntryStatus::POSTED,
                    'amount' => $line->amount->decimal(),
                    'currency' => $line->amount->currency->value(),
                    'account_balance_after' => $balanceAfter->decimal(),
                    'reversal_of_entry_id' => $line->metadata['reversal_of_entry_id'] ?? null,
                    'posted_at' => now(),
                    'metadata' => $line->metadata,
                ]);
            }

            LedgerEntryPosted::dispatch($batch);
            LedgerBatchCompleted::dispatch($batch);

            return $batch->refresh();
        });
    }
}
