<?php

namespace App\Domain\Reporting\DTOs;

use App\Domain\Reporting\Enums\AnalyticsSourceDomain;
use Illuminate\Support\Carbon;

class AnalyticsEventData
{
    /**
     * @param array<string, mixed> $dimensions
     * @param array<string, mixed> $measures
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly string $eventName,
        public readonly AnalyticsSourceDomain $sourceDomain,
        public readonly string $eventReference,
        public readonly Carbon $occurredAt,
        public readonly ?string $tenantId = null,
        public readonly array $dimensions = [],
        public readonly array $measures = [],
        public readonly array $payload = [],
    ) {
    }
}
