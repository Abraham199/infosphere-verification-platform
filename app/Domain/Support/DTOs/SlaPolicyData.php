<?php

namespace App\Domain\Support\DTOs;

class SlaPolicyData
{
    /**
     * @param array<string, mixed> $businessHours
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $name,
        public readonly int $firstResponseMinutes,
        public readonly int $resolutionMinutes,
        public readonly ?int $escalationMinutes = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $priorityId = null,
        public readonly array $businessHours = [],
        public readonly array $metadata = [],
    ) {
    }
}
