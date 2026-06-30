<?php

namespace App\Domain\Developer\Interfaces;

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
use Illuminate\Support\Collection;

interface DeveloperRepositoryInterface
{
    public function createApplication(array $attributes): DeveloperApplication;
    public function createClient(array $attributes): ApiClient;
    public function createApiKey(array $attributes): ApiKey;
    public function findApiKeyByHash(string $hash): ?ApiKey;
    public function createToken(array $attributes): ApiToken;
    public function findTokenByHash(string $hash): ?ApiToken;
    public function findVersion(string $version): ?ApiVersion;
    public function defaultVersion(): ?ApiVersion;
    public function createUsageLog(array $attributes): ApiUsageLog;
    public function createRateLimit(array $attributes): ApiRateLimit;
    public function findRateLimit(?string $tenantId, ?string $clientId, ?string $endpoint, ?string $productCode): ?ApiRateLimit;
    public function recentUsageCount(?string $tenantId, ?string $clientId, ?string $endpoint, int $windowSeconds): int;
    public function webhookEndpointExists(?string $tenantId, string $url, string $environment): bool;
    public function createWebhookEndpoint(array $attributes): WebhookEndpoint;
    public function createWebhookEvent(array $attributes): WebhookEvent;
    public function subscribedEndpoints(string $eventType, ?string $tenantId, string $environment): Collection;
    public function createWebhookDelivery(array $attributes): WebhookDelivery;
}
