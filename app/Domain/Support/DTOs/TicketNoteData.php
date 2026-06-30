<?php

namespace App\Domain\Support\DTOs;

class TicketNoteData
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $ticketId,
        public readonly string $body,
        public readonly ?string $authorId = null,
        public readonly bool $internal = false,
        public readonly array $metadata = [],
    ) {
    }
}
