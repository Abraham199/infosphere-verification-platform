<?php

namespace App\Http\Controllers\Tenant;

use App\Domain\Tenancy\Services\TenantContext;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class CustomerDashboardController extends Controller
{
    public function __construct(private readonly TenantContext $tenantContext)
    {
    }

    public function __invoke(): View
    {
        return view('customer.dashboard', [
            'tenant' => $this->tenantContext->require(),
        ]);
    }
}
