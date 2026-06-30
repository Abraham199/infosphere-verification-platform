<?php

namespace App\Domain\Verification\DTOs;

use App\Domain\Wallet\Models\Wallet;

readonly class VerificationRequestData
{
    public function __construct(
        public string $tenantId,
        public Wallet $wallet,
        public string $serviceCode,
        public string $reference,
        public array $payload,
        public ?string $subjectIdentifier = null,
        public ?string $idempotencyKey = null,
        public array $metadata = [],
    ) {
    }
}
