<?php

namespace App\Contracts\Verification;

use App\Domain\Verification\DTOs\VerificationProviderResponse;

interface VerificationProviderContract
{
    public function supports(string $providerServiceCode): bool;

    public function submit(string $providerServiceCode, array $payload, string $reference): VerificationProviderResponse;

    public function normalize(array $providerResponse): VerificationProviderResponse;
}
