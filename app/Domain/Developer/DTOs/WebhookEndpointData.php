<?php

namespace App\Domain\Developer\DTOs;

use App\Domain\Developer\Enums\DeveloperEnvironment;

class WebhookEndpointData
{
    /**
     * @param array<int, string> $events
     */
    public function __construct(
        public readonly string $url,
        public readonly array $events,
        public readonly ?string $tenantId = null,
        public readonly ?string $apiClientId = null,
        public readonly DeveloperEnvironment $environment = DeveloperEnvironment::PRODUCTION,
    ) {
    }
}
