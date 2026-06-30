<?php

namespace App\Domain\Product\Interfaces;

interface ProductCapabilityResolverInterface
{
    public function enabled(string $productId, string $capabilityKey): bool;
    public function all(string $productId): array;
}
