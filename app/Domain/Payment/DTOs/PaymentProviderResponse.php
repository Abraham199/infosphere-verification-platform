<?php

namespace App\Domain\Payment\DTOs;

readonly class PaymentProviderResponse
{
    public function __construct(
        public bool $successful,
        public string $status,
        public ?string $providerReference = null,
        public ?string $authorizationUrl = null,
        public ?string $paymentMethod = null,
        public ?string $paidAt = null,
        public ?string $failureReason = null,
        public array $payload = [],
    ) {
    }
}
