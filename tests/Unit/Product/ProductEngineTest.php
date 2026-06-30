<?php

namespace Tests\Unit\Product;

use App\Domain\Product\DTOs\ProductData;
use App\Domain\Product\DTOs\ProviderMappingData;
use App\Domain\Product\DTOs\TenantProductData;
use App\Domain\Product\Enums\ProductCapability;
use App\Domain\Product\Enums\ProductStatus;
use App\Domain\Product\Exceptions\DuplicateProductCodeException;
use App\Domain\Product\Exceptions\InvalidProductConfigurationException;
use App\Domain\Product\Services\ProductCapabilityService;
use App\Domain\Product\Services\ProductCategoryService;
use App\Domain\Product\Services\ProductProviderMappingService;
use App\Domain\Product\Services\ProductProviderResolver;
use App\Domain\Product\Services\ProductService;
use App\Domain\Product\Services\TenantProductService;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_created_under_category(): void
    {
        $category = app(ProductCategoryService::class)->create('verification', 'Verification');

        $product = app(ProductService::class)->create(new ProductData(
            categoryId: $category->id,
            code: 'nin_verification',
            name: 'NIN Verification',
            description: 'Verify Nigerian NIN records.',
            defaultPrice: '50.00',
        ));

        $this->assertSame('nin_verification', $product->code);
        $this->assertSame(ProductStatus::DRAFT, $product->status);
    }

    public function test_duplicate_product_code_is_rejected(): void
    {
        $category = app(ProductCategoryService::class)->create('verification', 'Verification');
        app(ProductService::class)->create(new ProductData($category->id, 'nin_verification', 'NIN', 'NIN', '50.00'));

        $this->expectException(DuplicateProductCodeException::class);

        app(ProductService::class)->create(new ProductData($category->id, 'nin_verification', 'NIN 2', 'NIN', '60.00'));
    }

    public function test_provider_mapping_can_be_resolved_by_priority(): void
    {
        $product = $this->product();
        app(ProductProviderMappingService::class)->map(new ProviderMappingData($product->id, 'swiftverify', 'swift_nin', 10));

        $mapping = app(ProductProviderResolver::class)->resolve($product->id);

        $this->assertSame('swiftverify', $mapping->provider);
        $this->assertSame('swift_nin', $mapping->provider_product_code);
    }

    public function test_tenant_can_override_product_availability_and_price(): void
    {
        $tenant = Tenant::query()->create(['name' => 'Tenant', 'slug' => 'tenant-'.Str::lower((string) Str::ulid()), 'status' => 'active']);
        $product = $this->product();

        $tenantProduct = app(TenantProductService::class)->configure(new TenantProductData(
            tenantId: $tenant->id,
            productId: $product->id,
            isEnabled: true,
            sellingPriceOverride: '75.00',
            statusOverride: 'active',
        ));

        $this->assertTrue($tenantProduct->is_enabled);
        $this->assertSame('75.00', $tenantProduct->selling_price_override);
    }

    public function test_capability_engine_resolves_enabled_capabilities(): void
    {
        $product = $this->product();
        app(ProductCapabilityService::class)->set($product->id, ProductCapability::REQUIRES_WALLET->value, true);

        $this->assertTrue(app(ProductCapabilityService::class)->enabled($product->id, ProductCapability::REQUIRES_WALLET->value));
    }

    public function test_invalid_capability_combination_is_rejected(): void
    {
        $product = $this->product();

        $this->expectException(InvalidProductConfigurationException::class);

        app(ProductCapabilityService::class)->set($product->id, ProductCapability::REQUIRES_PAYMENT_FIRST->value, true);
    }

    public function test_product_can_be_activated_and_disabled(): void
    {
        $product = $this->product();

        $product = app(ProductService::class)->activate($product);
        $this->assertSame(ProductStatus::ACTIVE, $product->status);

        $product = app(ProductService::class)->disable($product);
        $this->assertSame(ProductStatus::DISABLED, $product->status);
    }

    private function product()
    {
        $category = app(ProductCategoryService::class)->create('verification-'.Str::lower((string) Str::ulid()), 'Verification');

        return app(ProductService::class)->create(new ProductData(
            categoryId: $category->id,
            code: 'product-'.Str::lower((string) Str::ulid()),
            name: 'NIN Verification',
            description: 'Verify NIN.',
            defaultPrice: '50.00',
        ));
    }
}
