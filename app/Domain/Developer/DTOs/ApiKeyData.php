<?php

namespace App\Domain\Developer\DTOs;

use App\Domain\Developer\Enums\DeveloperEnvironment;
use Illuminate\Support\Carbon;

class ApiKeyData
{
    /**
     * @param array<int, string> $scopes
     */
    public function __construct(
        public readonly string $apiClientId,
        public readonly string $name,
        public readonly ?string $tenantId = null,
        public readonly DeveloperEnvironment $environment = DeveloperEnvironment::PRODUCTION,
        public readonly array $scopes = [],
        public readonly ?Carbon $expiresAt = null,
    ) {
    }
}
