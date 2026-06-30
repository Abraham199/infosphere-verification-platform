<?php

namespace App\Domain\Support\DTOs;

class KnowledgeArticleData
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly string $categoryId,
        public readonly string $slug,
        public readonly string $title,
        public readonly string $body,
        public readonly ?string $tenantId = null,
        public readonly ?string $authorId = null,
        public readonly array $metadata = [],
    ) {
    }
}
