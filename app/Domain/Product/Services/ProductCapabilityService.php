<?php

namespace App\Domain\Product\Services;

use App\Domain\Product\Interfaces\ProductCapabilityResolverInterface;
use App\Domain\Product\Models\ProductCapabilitySetting;
use App\Domain\Product\Validators\ProductValidationService;

class ProductCapabilityService implements ProductCapabilityResolverInterface
{
    public function __construct(private readonly ProductValidationService $validator)
    {
    }

    public function set(string $productId, string $capabilityKey, bool $enabled = true, array $configuration = []): ProductCapabilitySetting
    {
        $current = $this->all($productId);
        $current[$capabilityKey] = $enabled;
        $this->validator->assertFeatureCombinationValid($current);

        return ProductCapabilitySetting::query()->updateOrCreate([
            'product_id' => $productId,
            'capability_key' => $capabilityKey,
        ], [
            'is_enabled' => $enabled,
            'configuration' => $configuration,
        ]);
    }

    public function enabled(string $productId, string $capabilityKey): bool
    {
        return (bool) ProductCapabilitySetting::query()
            ->where('product_id', $productId)
            ->where('capability_key', $capabilityKey)
            ->value('is_enabled');
    }

    public function all(string $productId): array
    {
        return ProductCapabilitySetting::query()
            ->where('product_id', $productId)
            ->pluck('is_enabled', 'capability_key')
            ->map(fn ($value) => (bool) $value)
            ->all();
    }
}
