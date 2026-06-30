<?php

namespace App\Contracts\Payments;

use App\Domain\Payment\DTOs\PaymentProviderResponse;

interface PaymentProviderContract
{
    public function initialize(array $payload): PaymentProviderResponse;

    public function verify(string $reference): PaymentProviderResponse;

    public function verifyWebhookSignature(string $payload, ?string $signature): bool;
}
