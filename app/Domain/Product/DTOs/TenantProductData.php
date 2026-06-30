<?php

namespace App\Domain\Product\DTOs;

readonly class TenantProductData
{
    public function __construct(
        public string $tenantId,
        public string $productId,
        public bool $isEnabled,
        public ?string $sellingPriceOverride = null,
        public ?string $statusOverride = null,
        public array $accessRules = [],
        public array $metadata = [],
    ) {
    }
}
