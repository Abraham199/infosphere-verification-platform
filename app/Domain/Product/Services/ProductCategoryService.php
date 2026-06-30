<?php

namespace App\Domain\Product\Services;

use App\Domain\Product\Enums\ProductCategoryStatus;
use App\Domain\Product\Models\ProductCategory;
use App\Domain\Product\Validators\ProductValidationService;

class ProductCategoryService
{
    public function __construct(private readonly ProductValidationService $validator)
    {
    }

    public function create(string $code, string $name, ?string $description = null, int $displayOrder = 100, array $metadata = []): ProductCategory
    {
        $this->validator->assertCategoryCodeUnique($code);

        return ProductCategory::query()->create([
            'code' => $code,
            'name' => $name,
            'description' => $description,
            'status' => ProductCategoryStatus::ACTIVE,
            'display_order' => $displayOrder,
            'metadata' => $metadata,
        ]);
    }
}
