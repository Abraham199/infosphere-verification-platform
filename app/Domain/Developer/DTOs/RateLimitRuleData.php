<?php

namespace App\Domain\Developer\DTOs;

class RateLimitRuleData
{
    public function __construct(
        public readonly int $sustainedLimit,
        public readonly int $burstLimit,
        public readonly int $windowSeconds = 60,
        public readonly ?string $tenantId = null,
        public readonly ?string $apiClientId = null,
        public readonly ?string $productCode = null,
        public readonly ?string $endpoint = null,
    ) {
    }
}
