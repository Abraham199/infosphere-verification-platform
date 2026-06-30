<?php

namespace App\Domain\Product\Repositories;

use App\Domain\Product\Interfaces\ProductRepositoryInterface;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductCategory;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function categoryCodeExists(string $code): bool { return ProductCategory::query()->where('code', $code)->exists(); }
    public function productCodeExists(string $code): bool { return Product::query()->where('code', $code)->exists(); }
    public function findCategory(string $categoryId): ?ProductCategory { return ProductCategory::query()->whereKey($categoryId)->first(); }
    public function findProduct(string $productId): ?Product { return Product::query()->whereKey($productId)->first(); }
    public function findProductByCode(string $code): ?Product { return Product::query()->where('code', $code)->first(); }
}
