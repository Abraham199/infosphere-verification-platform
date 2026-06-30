<?php

namespace App\Domain\Developer\Repositories;

use App\Domain\Developer\Models\ApiClient;
use App\Domain\Developer\Models\ApiKey;
use App\Domain\Developer\Models\ApiRateLimit;
use App\Domain\Developer\Models\ApiToken;
use App\Domain\Developer\Models\ApiUsageLog;
use App\Domain\Developer\Models\ApiVersion;
use App\Domain\Developer\Models\DeveloperApplication;
use App\Domain\Developer\Models\WebhookDelivery;
use App\Domain\Developer\Models\WebhookEndpoint;
use App\Domain\Developer\Models\WebhookEvent;
use App\Domain\Developer\Interfaces\DeveloperRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentDeveloperRepository implements DeveloperRepositoryInterface
{
    public function createApplication(array $attributes): DeveloperApplication { return DeveloperApplication::query()->create($attributes); }
    public function createClient(array $attributes): ApiClient { return ApiClient::query()->create($attributes); }
    public function createApiKey(array $attributes): ApiKey { return ApiKey::query()->create($attributes); }
    public function findApiKeyByHash(string $hash): ?ApiKey { return ApiKey::query()->where('key_hash', $hash)->first(); }
    public function createToken(array $attributes): ApiToken { return ApiToken::query()->create($attributes); }
    public function findTokenByHash(string $hash): ?ApiToken { return ApiToken::query()->where('token_hash', $hash)->first(); }
    public function findVersion(string $version): ?ApiVersion { return ApiVersion::query()->where('version', $version)->first(); }
    public function defaultVersion(): ?ApiVersion { return ApiVersion::query()->where('is_default', true)->first(); }
    public function createUsageLog(array $attributes): ApiUsageLog { return ApiUsageLog::query()->create($attributes); }
    public function createRateLimit(array $attributes): ApiRateLimit { return ApiRateLimit::query()->create($attributes); }

    public function findRateLimit(?string $tenantId, ?string $clientId, ?string $endpoint, ?string $productCode): ?ApiRateLimit
    {
        return ApiRateLimit::query()
            ->where('is_active', true)
            ->where(function ($query) use ($tenantId): void {
                $query->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->where(function ($query) use ($clientId): void {
                $query->where('api_client_id', $clientId)->orWhereNull('api_client_id');
            })
            ->where(function ($query) use ($endpoint): void {
                $query->where('endpoint', $endpoint)->orWhereNull('endpoint');
            })
            ->where(function ($query) use ($productCode): void {
                $query->where('product_code', $productCode)->orWhereNull('product_code');
            })
            ->orderByRaw('CASE WHEN api_client_id IS NOT NULL THEN 1 ELSE 0 END DESC')
            ->orderByRaw('CASE WHEN endpoint IS NOT NULL THEN 1 ELSE 0 END DESC')
            ->first();
    }

    public function recentUsageCount(?string $tenantId, ?string $clientId, ?string $endpoint, int $windowSeconds): int
    {
        return ApiUsageLog::query()
            ->where('created_at', '>=', now()->subSeconds($windowSeconds))
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($clientId, fn ($query) => $query->where('api_client_id', $clientId))
            ->when($endpoint, fn ($query) => $query->where('endpoint', $endpoint))
            ->count();
    }

    public function webhookEndpointExists(?string $tenantId, string $url, string $environment): bool
    {
        return WebhookEndpoint::query()->where('tenant_id', $tenantId)->where('url', $url)->where('environment', $environment)->exists();
    }

    public function createWebhookEndpoint(array $attributes): WebhookEndpoint { return WebhookEndpoint::query()->create($attributes); }
    public function createWebhookEvent(array $attributes): WebhookEvent { return WebhookEvent::query()->create($attributes); }

    public function subscribedEndpoints(string $eventType, ?string $tenantId, string $environment): Collection
    {
        return WebhookEndpoint::query()
            ->where('environment', $environment)
            ->where('status', 'active')
            ->where(function ($query) use ($tenantId): void {
                $query->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->whereJsonContains('subscribed_events', $eventType)
            ->get();
    }

    public function createWebhookDelivery(array $attributes): WebhookDelivery { return WebhookDelivery::query()->create($attributes); }
}
