<?php

namespace App\Domain\Wallet\Repositories;

use App\Domain\Wallet\Interfaces\WalletRepositoryInterface;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\Models\WalletTransaction;

class EloquentWalletRepository implements WalletRepositoryInterface
{
    public function findForTenant(string $tenantId, string $currency): ?Wallet
    {
        return Wallet::query()
            ->where('tenant_id', $tenantId)
            ->where('currency', strtoupper($currency))
            ->first();
    }

    public function findLocked(string $walletId): Wallet
    {
        return Wallet::query()
            ->whereKey($walletId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    public function referenceExists(string $reference): bool
    {
        return WalletTransaction::query()->where('reference', $reference)->exists();
    }

    public function idempotencyKeyExists(?string $idempotencyKey): ?WalletTransaction
    {
        if ($idempotencyKey === null) {
            return null;
        }

        return WalletTransaction::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }
}
