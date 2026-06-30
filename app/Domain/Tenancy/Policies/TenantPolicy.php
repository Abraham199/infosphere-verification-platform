<?php

namespace App\Domain\Tenancy\Policies;

use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isPlatformUser() && $actor->hasPermissionTo('tenants.view');
    }

    public function view(User $actor, Tenant $tenant): bool
    {
        return $actor->isPlatformUser()
            || ($actor->tenant_id === $tenant->id && $actor->hasPermissionTo('tenant.profile.view'));
    }

    public function update(User $actor, Tenant $tenant): bool
    {
        return $actor->isPlatformUser()
            || ($actor->tenant_id === $tenant->id && $actor->hasPermissionTo('tenant.profile.update'));
    }
}
