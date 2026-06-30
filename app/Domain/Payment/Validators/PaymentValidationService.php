<?php

namespace App\Domain\Payment\Validators;

use App\Domain\Payment\Exceptions\DuplicatePaymentReferenceException;
use App\Domain\Payment\Exceptions\InvalidPaymentStateException;
use App\Domain\Payment\Interfaces\PaymentRepositoryInterface;
use App\Domain\Payment\Models\PaymentWebhook;
use App\Domain\Wallet\ValueObjects\Money;

class PaymentValidationService
{
    public function __construct(private readonly PaymentRepositoryInterface $payments)
    {
    }

    public function assertReferenceIsValid(string $reference): void
    {
        if (! preg_match('/^[A-Z0-9_\-]{12,100}$/', $reference)) {
            throw new InvalidPaymentStateException('Payment reference format is invalid.');
        }
    }

    public function assertReferenceIsUnique(string $reference): void
    {
        if ($this->payments->referenceExists($reference)) {
            throw new DuplicatePaymentReferenceException('Payment reference already exists.');
        }
    }

    public function assertPositiveAmount(Money $amount): void
    {
        if ($amount->isZero()) {
            throw new InvalidPaymentStateException('Payment amount must be greater than zero.');
        }
    }

    public function assertWebhookIsNotReplay(?string $eventReference, ?string $signatureHash): void
    {
        if ($eventReference !== null && PaymentWebhook::query()->where('event_reference', $eventReference)->exists()) {
            throw new InvalidPaymentStateException('Duplicate payment webhook event.');
        }

        if ($signatureHash !== null && PaymentWebhook::query()->where('signature_hash', $signatureHash)->exists()) {
            throw new InvalidPaymentStateException('Duplicate payment webhook signature.');
        }
    }
}
