<?php

namespace App\Domain\Support\DTOs;

class TicketData
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $subject,
        public readonly string $description,
        public readonly ?string $tenantId = null,
        public readonly ?string $requesterId = null,
        public readonly ?string $categoryId = null,
        public readonly ?string $priorityId = null,
        public readonly ?string $reference = null,
        public readonly string $source = 'portal',
        public readonly array $metadata = [],
    ) {
    }
}
