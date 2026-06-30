<?php

namespace App\Domain\Payment\DTOs;

readonly class PaymentWebhookData
{
    public function __construct(
        public string $provider,
        public string $eventType,
        public ?string $eventReference,
        public ?string $transactionReference,
        public string $rawPayload,
        public array $payload,
        public ?string $signature,
    ) {
    }
}
