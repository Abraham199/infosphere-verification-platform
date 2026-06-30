<?php

namespace App\Domain\Payment\Interfaces;

use App\Domain\Payment\DTOs\PaymentInitializationData;
use App\Domain\Payment\DTOs\PaymentWebhookData;
use App\Domain\Payment\Models\PaymentTransaction;

interface PaymentServiceInterface
{
    public function initialize(PaymentInitializationData $data): PaymentTransaction;

    public function verify(string $reference): PaymentTransaction;

    public function handleWebhook(PaymentWebhookData $webhook): PaymentTransaction;
}
