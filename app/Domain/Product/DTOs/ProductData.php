<?php

namespace App\Domain\Product\DTOs;

readonly class ProductData
{
    public function __construct(
        public string $categoryId,
        public string $code,
        public string $name,
        public string $description,
        public string $defaultPrice,
        public ?string $costPrice = null,
        public ?string $sellingPrice = null,
        public string $currency = 'NGN',
        public string $visibility = 'private',
        public array $metadata = [],
    ) {
    }
}
