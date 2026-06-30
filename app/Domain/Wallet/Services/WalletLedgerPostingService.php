<?php

namespace App\Domain\Wallet\Services;

use App\Domain\Ledger\Enums\LedgerEntryType;
use App\Domain\Ledger\Interfaces\LedgerPostingInterface;
use App\Domain\Ledger\Models\LedgerBatch;
use App\Domain\Ledger\ValueObjects\JournalEntry;
use App\Domain\Ledger\ValueObjects\LedgerLine;
use App\Domain\Wallet\Interfaces\WalletLedgerPostingInterface;
use App\Domain\Wallet\Models\WalletTransaction;
use App\Domain\Wallet\ValueObjects\Currency;
use App\Domain\Wallet\ValueObjects\Money;

class WalletLedgerPostingService implements WalletLedgerPostingInterface
{
    public function __construct(private readonly LedgerPostingInterface $ledger)
    {
    }

    public function postWalletCredit(WalletTransaction $transaction, string $walletLiabilityAccountId, string $offsetAccountId): LedgerBatch
    {
        $amount = Money::fromDecimal($transaction->amount, $transaction->currency);

        return $this->ledger->post(new JournalEntry(
            tenantId: $transaction->tenant_id,
            reference: 'LEDGER-'.$transaction->reference,
            description: 'Wallet credit posting',
            currency: new Currency($transaction->currency),
            lines: [
                new LedgerLine($offsetAccountId, LedgerEntryType::DEBIT, $amount),
                new LedgerLine($walletLiabilityAccountId, LedgerEntryType::CREDIT, $amount),
            ],
            metadata: ['wallet_transaction_id' => $transaction->id],
            sourceType: $transaction::class,
            sourceId: $transaction->id,
        ));
    }

    public function postWalletDebit(WalletTransaction $transaction, string $walletLiabilityAccountId, string $offsetAccountId): LedgerBatch
    {
        $amount = Money::fromDecimal($transaction->amount, $transaction->currency);

        return $this->ledger->post(new JournalEntry(
            tenantId: $transaction->tenant_id,
            reference: 'LEDGER-'.$transaction->reference,
            description: 'Wallet debit posting',
            currency: new Currency($transaction->currency),
            lines: [
                new LedgerLine($walletLiabilityAccountId, LedgerEntryType::DEBIT, $amount),
                new LedgerLine($offsetAccountId, LedgerEntryType::CREDIT, $amount),
            ],
            metadata: ['wallet_transaction_id' => $transaction->id],
            sourceType: $transaction::class,
            sourceId: $transaction->id,
        ));
    }
}
