<?php

namespace App\Domain\Verification\DTOs;

readonly class VerificationProviderResponse
{
    public function __construct(
        public bool $successful,
        public string $status,
        public ?string $providerReference = null,
        public ?string $resultStatus = null,
        public ?float $confidenceScore = null,
        public ?string $summary = null,
        public array $normalizedData = [],
        public array $payload = [],
        public ?string $errorMessage = null,
    ) {
    }
}
