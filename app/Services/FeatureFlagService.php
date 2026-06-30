<?php

namespace App\Services;

use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Services\TenantContext;

class FeatureFlagService
{
    public function __construct(private readonly TenantContext $tenantContext)
    {
    }

    public function enabled(string $key, ?Tenant $tenant = null): bool
    {
        $tenant ??= $this->tenantContext->get();

        if ($tenant === null) {
            return (bool) config("ivp.features.{$key}", false);
        }

        $setting = $tenant->settings()
            ->where('group', 'features')
            ->where('key', $key)
            ->first();

        return $setting ? filter_var($setting->value, FILTER_VALIDATE_BOOL) : (bool) config("ivp.features.{$key}", false);
    }
}
