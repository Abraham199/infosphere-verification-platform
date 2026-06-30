<?php

namespace App\Domain\Developer\DTOs;

use App\Domain\Developer\Enums\DeveloperEnvironment;

class ApiClientData
{
    /**
     * @param array<int, string> $allowedScopes
     * @param array<int, string> $allowedIps
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $tenantId = null,
        public readonly ?string $developerApplicationId = null,
        public readonly DeveloperEnvironment $environment = DeveloperEnvironment::PRODUCTION,
        public readonly array $allowedScopes = [],
        public readonly array $allowedIps = [],
    ) {
    }
}
