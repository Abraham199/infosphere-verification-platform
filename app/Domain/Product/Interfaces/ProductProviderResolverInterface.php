<?php

namespace App\Domain\Product\Interfaces;

use App\Domain\Product\Models\ProductProviderMapping;

interface ProductProviderResolverInterface
{
    public function resolve(string $productId): ?ProductProviderMapping;
}
