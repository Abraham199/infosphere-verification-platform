<?php

namespace App\Http\Controllers\Tenant;

use App\Domain\Tenancy\Services\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\ViewModels\ExperienceDashboardData;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly ExperienceDashboardData $data,
    ) {
    }

    public function __invoke(): View
    {
        $tenant = $this->tenantContext->require();

        return view('tenant.products.index', [
            'tenant' => $tenant,
            ...$this->data->products($tenant),
        ]);
    }
}
