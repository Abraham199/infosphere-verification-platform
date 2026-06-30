<?php

namespace App\Domain\Wallet\Services;

use App\Domain\Wallet\Interfaces\WalletServiceInterface;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\Models\WalletTransaction;
use App\Domain\Wallet\ValueObjects\Money;

class WalletService implements WalletServiceInterface
{
    public function __construct(
        private readonly WalletCreditService $credits,
        private readonly WalletDebitService $debits,
    ) {
    }

    public function credit(Wallet $wallet, Money $amount, string $reference, ?string $idempotencyKey = null, array $metadata = []): WalletTransaction
    {
        return $this->credits->credit($wallet, $amount, $reference, $idempotencyKey, $metadata);
    }

    public function debit(Wallet $wallet, Money $amount, string $reference, ?string $idempotencyKey = null, array $metadata = []): WalletTransaction
    {
        return $this->debits->debit($wallet, $amount, $reference, $idempotencyKey, $metadata);
    }
}
