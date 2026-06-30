<?php

namespace App\Domain\Support\DTOs;

use App\Domain\Support\Enums\AssignmentType;

class AssignmentData
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $ticketId,
        public readonly AssignmentType $assignmentType = AssignmentType::MANUAL,
        public readonly ?string $assignedToUserId = null,
        public readonly ?string $assignedTeam = null,
        public readonly ?string $assignedBy = null,
        public readonly array $metadata = [],
    ) {
    }
}
