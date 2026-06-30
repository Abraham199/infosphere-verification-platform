<?php

namespace App\Domain\Support\DTOs;

class AttachmentData
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $ticketId,
        public readonly string $filename,
        public readonly string $mimeType,
        public readonly int $sizeBytes,
        public readonly string $storagePath,
        public readonly ?string $uploadedBy = null,
        public readonly array $metadata = [],
    ) {
    }
}
