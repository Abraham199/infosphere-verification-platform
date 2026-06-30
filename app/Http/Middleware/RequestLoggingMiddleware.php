<?php

namespace App\Http\Middleware;

use App\Domain\Tenancy\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestLoggingMiddleware
{
    public function __construct(private readonly TenantContext $tenantContext)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        $response = $next($request);

        if (config('ivp.security.request_logging_enabled', true)) {
            Log::info('request.completed', [
                'tenant_id' => $this->tenantContext->id(),
                'user_id' => $request->user()?->id,
                'method' => $request->method(),
                'path' => $request->path(),
                'status' => $response->getStatusCode(),
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
                'ip' => $request->ip(),
            ]);
        }

        return $response;
    }
}
