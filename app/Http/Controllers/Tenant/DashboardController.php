<?php

namespace App\Http\Controllers\Tenant;

use App\Domain\Tenancy\Services\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\ViewModels\ExperienceDashboardData;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly ExperienceDashboardData $data,
    )
    {
    }

    public function __invoke(): View
    {
        $tenant = $this->tenantContext->require();

        return view('tenant.dashboard', [
            'tenant' => $tenant,
            ...$this->data->tenant($tenant),
        ]);
    }
}
