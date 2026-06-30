<?php

namespace App\Domain\Product\Services;

use App\Domain\Product\DTOs\ProviderMappingData;
use App\Domain\Product\Enums\ProviderMappingStatus;
use App\Domain\Product\Events\ProductProviderChanged;
use App\Domain\Product\Models\ProductProviderMapping;
use App\Domain\Product\Validators\ProductValidationService;

class ProductProviderMappingService
{
    public function __construct(private readonly ProductValidationService $validator)
    {
    }

    public function map(ProviderMappingData $data): ProductProviderMapping
    {
        $this->validator->assertProviderMappingValid($data->provider, $data->providerProductCode);

        $mapping = ProductProviderMapping::query()->updateOrCreate([
            'product_id' => $data->productId,
            'provider' => $data->provider,
        ], [
            'provider_product_code' => $data->providerProductCode,
            'status' => ProviderMappingStatus::ACTIVE,
            'priority' => $data->priority,
            'configuration' => $data->configuration,
        ]);

        ProductProviderChanged::dispatch($mapping->product);

        return $mapping;
    }

    public function updateStatus(ProductProviderMapping $mapping, ProviderMappingStatus $status): ProductProviderMapping
    {
        $mapping->forceFill(['status' => $status])->save();
        ProductProviderChanged::dispatch($mapping->product);

        return $mapping->refresh();
    }
}
