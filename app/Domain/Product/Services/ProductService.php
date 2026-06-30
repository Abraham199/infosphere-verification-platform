<?php

namespace App\Domain\Product\Services;

use App\Domain\Product\DTOs\ProductData;
use App\Domain\Product\Enums\ProductStatus;
use App\Domain\Product\Enums\ProductVisibility;
use App\Domain\Product\Events\ProductActivated;
use App\Domain\Product\Events\ProductCreated;
use App\Domain\Product\Events\ProductDisabled;
use App\Domain\Product\Events\ProductPriceChanged;
use App\Domain\Product\Events\ProductUpdated;
use App\Domain\Product\Interfaces\ProductRepositoryInterface;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Validators\ProductValidationService;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductValidationService $validator,
        private readonly ProductStateMachine $states,
    ) {
    }

    public function create(ProductData $data): Product
    {
        $this->validator->assertProductCodeUnique($data->code);
        $this->validator->assertCategoryValid($this->products->findCategory($data->categoryId));

        $product = Product::query()->create([
            'product_category_id' => $data->categoryId,
            'code' => $data->code,
            'name' => $data->name,
            'description' => $data->description,
            'default_price' => $data->defaultPrice,
            'cost_price' => $data->costPrice,
            'selling_price' => $data->sellingPrice,
            'currency' => $data->currency,
            'status' => ProductStatus::DRAFT,
            'visibility' => ProductVisibility::tryFrom($data->visibility) ?? ProductVisibility::PRIVATE,
            'metadata' => $data->metadata,
        ]);

        ProductCreated::dispatch($product);

        return $product;
    }

    public function activate(Product $product): Product
    {
        $product = $this->states->transition($product, ProductStatus::ACTIVE);
        ProductActivated::dispatch($product);

        return $product;
    }

    public function disable(Product $product): Product
    {
        $product = $this->states->transition($product, ProductStatus::DISABLED);
        ProductDisabled::dispatch($product);

        return $product;
    }

    public function updatePrice(Product $product, string $sellingPrice): Product
    {
        $product->forceFill(['selling_price' => $sellingPrice])->save();
        ProductPriceChanged::dispatch($product);
        ProductUpdated::dispatch($product);

        return $product->refresh();
    }

    public function update(Product $product, ProductData $data): Product
    {
        $this->validator->assertCategoryValid($this->products->findCategory($data->categoryId));

        $product->forceFill([
            'product_category_id' => $data->categoryId,
            'name' => $data->name,
            'description' => $data->description,
            'default_price' => $data->defaultPrice,
            'cost_price' => $data->costPrice,
            'selling_price' => $data->sellingPrice,
            'currency' => $data->currency,
            'visibility' => ProductVisibility::tryFrom($data->visibility) ?? ProductVisibility::PRIVATE,
            'metadata' => $data->metadata,
        ])->save();

        ProductUpdated::dispatch($product);

        return $product->refresh();
    }
}
