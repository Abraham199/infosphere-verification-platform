<?php

namespace App\Domain\Verification\Validators;

use App\Domain\Verification\Enums\VerificationServiceStatus;
use App\Domain\Verification\Exceptions\DuplicateVerificationRequestException;
use App\Domain\Verification\Exceptions\VerificationPricingException;
use App\Domain\Verification\Exceptions\VerificationProviderException;
use App\Domain\Verification\Interfaces\VerificationRepositoryInterface;
use App\Domain\Verification\Models\ProviderService;
use App\Domain\Verification\Models\VerificationService;
use App\Domain\Wallet\ValueObjects\Money;

class VerificationValidationService
{
    public function __construct(private readonly VerificationRepositoryInterface $verifications)
    {
    }

    public function assertReferenceIsValid(string $reference): void
    {
        if (! preg_match('/^[A-Z0-9_\-]{12,100}$/', $reference)) {
            throw new DuplicateVerificationRequestException('Verification reference format is invalid.');
        }
    }

    public function assertReferenceIsUnique(string $reference): void
    {
        if ($this->verifications->referenceExists($reference)) {
            throw new DuplicateVerificationRequestException('Verification reference already exists.');
        }
    }

    public function assertServiceAvailable(VerificationService $service): void
    {
        if ($service->status !== VerificationServiceStatus::ACTIVE) {
            throw new VerificationProviderException('Verification service is not active.');
        }
    }

    public function assertProviderServiceAvailable(?ProviderService $providerService): ProviderService
    {
        if ($providerService === null || $providerService->status !== VerificationServiceStatus::ACTIVE) {
            throw new VerificationProviderException('No active provider service is available.');
        }

        return $providerService;
    }

    public function assertPricingAvailable(Money $price): void
    {
        if ($price->isZero()) {
            throw new VerificationPricingException('Verification pricing is not available.');
        }
    }
}
