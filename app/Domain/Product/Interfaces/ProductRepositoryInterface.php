<?php

namespace App\Domain\Product\Interfaces;

use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductCategory;

interface ProductRepositoryInterface
{
    public function categoryCodeExists(string $code): bool;
    public function productCodeExists(string $code): bool;
    public function findCategory(string $categoryId): ?ProductCategory;
    public function findProduct(string $productId): ?Product;
    public function findProductByCode(string $code): ?Product;
}
