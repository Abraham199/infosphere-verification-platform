<?php

namespace App\Domain\Wallet\Interfaces;

use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\ValueObjects\Money;

interface WalletServiceInterface
{
    public function credit(Wallet $wallet, Money $amount, string $reference, ?string $idempotencyKey = null, array $metadata = []): mixed;

    public function debit(Wallet $wallet, Money $amount, string $reference, ?string $idempotencyKey = null, array $metadata = []): mixed;
}
