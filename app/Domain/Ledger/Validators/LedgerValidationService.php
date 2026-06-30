<?php

namespace App\Domain\Ledger\Validators;

use App\Domain\Ledger\Enums\LedgerEntryStatus;
use App\Domain\Ledger\Enums\LedgerEntryType;
use App\Domain\Ledger\Exceptions\DuplicateLedgerReferenceException;
use App\Domain\Ledger\Exceptions\InvalidLedgerPostingSequenceException;
use App\Domain\Ledger\Exceptions\InvalidLedgerReversalException;
use App\Domain\Ledger\Exceptions\MissingLedgerAccountException;
use App\Domain\Ledger\Exceptions\UnbalancedJournalEntryException;
use App\Domain\Ledger\Interfaces\LedgerRepositoryInterface;
use App\Domain\Ledger\Models\LedgerBatch;
use App\Domain\Ledger\ValueObjects\JournalEntry;

class LedgerValidationService
{
    public function __construct(private readonly LedgerRepositoryInterface $ledger)
    {
    }

    public function assertCanPost(JournalEntry $journalEntry): void
    {
        if ($this->ledger->batchReferenceExists($journalEntry->reference)) {
            throw new DuplicateLedgerReferenceException('Ledger batch reference already exists.');
        }

        $debits = 0;
        $credits = 0;

        foreach ($journalEntry->lines as $index => $line) {
            $account = $this->ledger->findAccount($line->accountId);

            if ($account === null || ! $account->is_active) {
                throw new MissingLedgerAccountException("Ledger account is missing or inactive at line {$index}.");
            }

            if ($account->tenant_id !== $journalEntry->tenantId) {
                throw new InvalidLedgerPostingSequenceException('Ledger account tenant does not match journal tenant.');
            }

            if ($account->currency !== $journalEntry->currency->value()) {
                throw new InvalidLedgerPostingSequenceException('Ledger account currency does not match journal currency.');
            }

            if ($line->amount->currency->value() !== $journalEntry->currency->value()) {
                throw new InvalidLedgerPostingSequenceException('Ledger line currency does not match journal currency.');
            }

            if ($line->amount->isZero()) {
                throw new InvalidLedgerPostingSequenceException('Ledger line amount must be greater than zero.');
            }

            if ($line->type === LedgerEntryType::DEBIT) {
                $debits += $line->amount->minorUnits;
            }

            if ($line->type === LedgerEntryType::CREDIT) {
                $credits += $line->amount->minorUnits;
            }
        }

        if ($debits !== $credits) {
            throw new UnbalancedJournalEntryException('Ledger journal entry is not balanced.');
        }
    }

    public function assertCanReverse(LedgerBatch $batch): void
    {
        if ($batch->status !== LedgerEntryStatus::POSTED) {
            throw new InvalidLedgerReversalException('Only posted ledger batches can be reversed.');
        }

        if ($batch->reversal_of_batch_id !== null) {
            throw new InvalidLedgerReversalException('Reversal batches cannot be reversed again.');
        }

        if (LedgerBatch::query()->where('reversal_of_batch_id', $batch->id)->exists()) {
            throw new InvalidLedgerReversalException('Ledger batch has already been reversed.');
        }
    }
}
