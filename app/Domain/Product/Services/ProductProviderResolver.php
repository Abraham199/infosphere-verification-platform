<?php

namespace App\Domain\Product\Services;

use App\Domain\Product\Enums\ProviderMappingStatus;
use App\Domain\Product\Interfaces\ProductProviderResolverInterface;
use App\Domain\Product\Models\ProductProviderMapping;

class ProductProviderResolver implements ProductProviderResolverInterface
{
    public function resolve(string $productId): ?ProductProviderMapping
    {
        return ProductProviderMapping::query()
            ->where('product_id', $productId)
            ->where('status', ProviderMappingStatus::ACTIVE)
            ->orderBy('priority')
            ->first();
    }
}
