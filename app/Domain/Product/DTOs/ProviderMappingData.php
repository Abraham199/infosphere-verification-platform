<?php

namespace App\Domain\Product\DTOs;

readonly class ProviderMappingData
{
    public function __construct(
        public string $productId,
        public string $provider,
        public string $providerProductCode,
        public int $priority = 100,
        public array $configuration = [],
    ) {
    }
}
