<?php

namespace App\Domain\Payment\DTOs;

use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\ValueObjects\Money;

readonly class PaymentInitializationData
{
    public function __construct(
        public string $tenantId,
        public Wallet $wallet,
        public Money $amount,
        public string $email,
        public string $reference,
        public ?string $callbackUrl = null,
        public ?string $idempotencyKey = null,
        public array $metadata = [],
    ) {
    }
}
