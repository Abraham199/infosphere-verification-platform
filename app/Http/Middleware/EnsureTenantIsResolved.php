<?php

namespace App\Http\Middleware;

use App\Domain\Tenancy\Services\TenantContext;
use App\Domain\Tenancy\Services\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsResolved
{
    public function __construct(
        private readonly TenantResolver $resolver,
        private readonly TenantContext $context,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolver->resolve($request);

        abort_if($tenant === null, 404, 'Tenant not found.');
        abort_unless($tenant->isActive(), 403, 'Tenant is not active.');

        $this->context->set($tenant);
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
