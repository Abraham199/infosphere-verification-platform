<?php

namespace App\Domain\Wallet\Interfaces;

use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\Models\WalletTransaction;

interface WalletRepositoryInterface
{
    public function findForTenant(string $tenantId, string $currency): ?Wallet;

    public function findLocked(string $walletId): Wallet;

    public function referenceExists(string $reference): bool;

    public function idempotencyKeyExists(?string $idempotencyKey): ?WalletTransaction;
}
