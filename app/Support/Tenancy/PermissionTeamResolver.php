<?php

namespace App\Support\Tenancy;

use App\Domain\Tenancy\Services\TenantContext;
use Spatie\Permission\Contracts\PermissionsTeamResolver;

class PermissionTeamResolver implements PermissionsTeamResolver
{
    public function getPermissionsTeamId(): ?string
    {
        return app(TenantContext::class)->id();
    }

    public function setPermissionsTeamId($id): void
    {
        // Tenant team context is resolved centrally through TenantContext.
    }
}
