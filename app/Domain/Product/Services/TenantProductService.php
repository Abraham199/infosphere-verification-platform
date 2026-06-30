<?php

namespace App\Domain\Product\Services;

use App\Domain\Product\DTOs\TenantProductData;
use App\Domain\Product\Models\TenantProduct;
use App\Domain\Product\Validators\ProductValidationService;

class TenantProductService
{
    public function __construct(private readonly ProductValidationService $validator)
    {
    }

    public function configure(TenantProductData $data): TenantProduct
    {
        $this->validator->assertTenantOverrideValid($data->sellingPriceOverride, $data->statusOverride);

        return TenantProduct::query()->updateOrCreate([
            'tenant_id' => $data->tenantId,
            'product_id' => $data->productId,
        ], [
            'is_enabled' => $data->isEnabled,
            'selling_price_override' => $data->sellingPriceOverride,
            'status_override' => $data->statusOverride,
            'access_rules' => $data->accessRules,
            'metadata' => $data->metadata,
        ]);
    }
}
