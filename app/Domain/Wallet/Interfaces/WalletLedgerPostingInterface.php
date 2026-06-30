<?php

namespace App\Domain\Wallet\Interfaces;

use App\Domain\Ledger\Models\LedgerBatch;
use App\Domain\Wallet\Models\WalletTransaction;

interface WalletLedgerPostingInterface
{
    public function postWalletCredit(WalletTransaction $transaction, string $walletLiabilityAccountId, string $offsetAccountId): LedgerBatch;

    public function postWalletDebit(WalletTransaction $transaction, string $walletLiabilityAccountId, string $offsetAccountId): LedgerBatch;
}
