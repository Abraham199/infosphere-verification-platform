<?php

namespace App\Domain\Verification\Interfaces;

use App\Domain\Verification\DTOs\VerificationProviderResponse;

interface VerificationProviderInterface
{
    public function supports(string $providerServiceCode): bool;

    public function submit(string $providerServiceCode, array $payload, string $reference): VerificationProviderResponse;
}
