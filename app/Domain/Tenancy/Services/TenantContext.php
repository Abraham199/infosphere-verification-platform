<?php

namespace App\Domain\Tenancy\Services;

use App\Domain\Tenancy\Models\Tenant;

class TenantContext
{
    private ?Tenant $tenant = null;

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?string
    {
        return $this->tenant?->id;
    }

    public function require(): Tenant
    {
        abort_if($this->tenant === null, 404, 'Tenant context has not been resolved.');

        return $this->tenant;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }
}
