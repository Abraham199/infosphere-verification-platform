<?php

namespace App\Domain\Tenancy\Events;

use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Tenant $tenant)
    {
    }
}
