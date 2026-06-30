<?php

namespace App\Domain\Product\Validators;

use App\Domain\Product\Exceptions\DuplicateProductCodeException;
use App\Domain\Product\Exceptions\InvalidProductConfigurationException;
use App\Domain\Product\Interfaces\ProductRepositoryInterface;
use App\Domain\Product\Models\ProductCategory;

class ProductValidationService
{
    public function __construct(private readonly ProductRepositoryInterface $products)
    {
    }

    public function assertCategoryCodeUnique(string $code): void
    {
        if ($this->products->categoryCodeExists($code)) {
            throw new DuplicateProductCodeException('Product category code already exists.');
        }
    }

    public function assertProductCodeUnique(string $code): void
    {
        if ($this->products->productCodeExists($code)) {
            throw new DuplicateProductCodeException('Product code already exists.');
        }
    }

    public function assertCategoryValid(?ProductCategory $category): ProductCategory
    {
        if ($category === null || $category->status->value !== 'active') {
            throw new InvalidProductConfigurationException('Product category is invalid or inactive.');
        }

        return $category;
    }

    public function assertProviderMappingValid(string $provider, string $providerProductCode): void
    {
        if ($provider === '' || $providerProductCode === '') {
            throw new InvalidProductConfigurationException('Provider and provider product code are required.');
        }
    }

    public function assertTenantOverrideValid(?string $price, ?string $status): void
    {
        if ($price !== null && (float) $price < 0) {
            throw new InvalidProductConfigurationException('Tenant product price override cannot be negative.');
        }

        if ($status !== null && ! in_array($status, ['draft', 'active', 'disabled', 'maintenance', 'deprecated', 'archived'], true)) {
            throw new InvalidProductConfigurationException('Tenant product status override is invalid.');
        }
    }

    public function assertFeatureCombinationValid(array $capabilities): void
    {
        if (($capabilities['requires_payment_first'] ?? false) && ! ($capabilities['requires_wallet'] ?? false)) {
            throw new InvalidProductConfigurationException('Products requiring payment first must also require wallet support.');
        }
    }
}
