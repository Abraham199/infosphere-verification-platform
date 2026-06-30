<?php

namespace App\Domain\Tenancy\Services;

use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Models\TenantDomain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TenantResolver
{
    public function resolve(Request $request): ?Tenant
    {
        return $this->resolveByPath($request)
            ?? $this->resolveByDomain($request);
    }

    private function resolveByPath(Request $request): ?Tenant
    {
        $prefix = trim((string) config('tenancy.path_prefix', 't'), '/');

        if ($request->segment(1) !== $prefix) {
            return null;
        }

        $slug = $request->segment(2);

        if (! $slug) {
            return null;
        }

        return Cache::remember("tenant:slug:{$slug}", config('tenancy.cache_ttl'), fn () => Tenant::query()
            ->where('slug', $slug)
            ->whereIn('status', ['active', 'trial'])
            ->first());
    }

    private function resolveByDomain(Request $request): ?Tenant
    {
        $host = strtolower($request->getHost());

        if (in_array($host, config('tenancy.central_domains', []), true)) {
            return null;
        }

        return Cache::remember("tenant:domain:{$host}", config('tenancy.cache_ttl'), function () use ($host) {
            return TenantDomain::query()
                ->with('tenant')
                ->where('domain', $host)
                ->where('verification_status', 'verified')
                ->first()
                ?->tenant;
        });
    }
}
