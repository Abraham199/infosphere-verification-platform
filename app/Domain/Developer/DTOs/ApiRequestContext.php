<?php

namespace App\Domain\Developer\DTOs;

class ApiRequestContext
{
    public function __construct(
        public readonly string $requestId,
        public readonly string $apiVersion,
        public readonly string $method,
        public readonly string $endpoint,
        public readonly ?string $tenantId = null,
        public readonly ?string $apiClientId = null,
        public readonly ?string $productCode = null,
        public readonly ?int $statusCode = null,
        public readonly ?int $latencyMs = null,
        public readonly bool $rateLimited = false,
        public readonly ?string $errorCode = null,
        public readonly array $metadata = [],
    ) {
    }
}
