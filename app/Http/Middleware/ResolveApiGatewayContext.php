<?php

namespace App\Http\Middleware;

use App\Domain\Developer\DTOs\ApiRequestContext;
use App\Domain\Developer\Interfaces\ApiGatewayInterface;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ResolveApiGatewayContext
{
    public function __construct(private readonly ApiGatewayInterface $gateway)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $startedAt = microtime(true);
        $requestId = $request->headers->get('X-Request-Id') ?? (string) Str::uuid();
        $version = $request->route('version') ?? $this->versionFromPath($request->path()) ?? 'v1';

        $this->gateway->accept(new ApiRequestContext(
            requestId: $requestId,
            apiVersion: $version,
            method: $request->method(),
            endpoint: '/'.$request->path(),
            tenantId: $request->headers->get('X-Tenant-Id'),
            apiClientId: $request->headers->get('X-Api-Client-Id'),
            productCode: $request->headers->get('X-Product-Code'),
            latencyMs: (int) ((microtime(true) - $startedAt) * 1000),
        ));

        $response = $next($request);
        $response->headers->set('X-Request-Id', $requestId);
        $response->headers->set('X-API-Version', $version);

        return $response;
    }

    private function versionFromPath(string $path): ?string
    {
        return preg_match('#^(v\d+)/#', $path, $matches) === 1 ? $matches[1] : null;
    }
}
