<?php

namespace App\Domain\Payment\Repositories;

use App\Domain\Payment\Interfaces\PaymentRepositoryInterface;
use App\Domain\Payment\Models\PaymentTransaction;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function referenceExists(string $reference): bool
    {
        return PaymentTransaction::query()->where('reference', $reference)->exists();
    }

    public function findByReference(string $reference): ?PaymentTransaction
    {
        return PaymentTransaction::query()->where('reference', $reference)->first();
    }

    public function findByIdempotencyKey(?string $idempotencyKey): ?PaymentTransaction
    {
        if ($idempotencyKey === null) {
            return null;
        }

        return PaymentTransaction::query()->where('idempotency_key', $idempotencyKey)->first();
    }

    public function findLocked(string $paymentTransactionId): PaymentTransaction
    {
        return PaymentTransaction::query()
            ->whereKey($paymentTransactionId)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
