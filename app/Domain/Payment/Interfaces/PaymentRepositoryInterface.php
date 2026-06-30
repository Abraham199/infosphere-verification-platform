<?php

namespace App\Domain\Payment\Interfaces;

use App\Domain\Payment\Models\PaymentTransaction;

interface PaymentRepositoryInterface
{
    public function referenceExists(string $reference): bool;

    public function findByReference(string $reference): ?PaymentTransaction;

    public function findByIdempotencyKey(?string $idempotencyKey): ?PaymentTransaction;

    public function findLocked(string $paymentTransactionId): PaymentTransaction;
}
