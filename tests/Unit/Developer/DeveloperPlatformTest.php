<?php

namespace Tests\Unit\Developer;

use App\Domain\Developer\DTOs\ApiClientData;
use App\Domain\Developer\DTOs\ApiKeyData;
use App\Domain\Developer\DTOs\ApiRequestContext;
use App\Domain\Developer\DTOs\ApiTokenData;
use App\Domain\Developer\DTOs\RateLimitRuleData;
use App\Domain\Developer\DTOs\WebhookEndpointData;
use App\Domain\Developer\Enums\DeveloperEnvironment;
use App\Domain\Developer\Exceptions\InvalidApiCredentialException;
use App\Domain\Developer\Exceptions\InvalidApiVersionException;
use App\Domain\Developer\Exceptions\InvalidWebhookException;
use App\Domain\Developer\Exceptions\RateLimitExceededException;
use App\Domain\Developer\Models\ApiVersion;
use App\Domain\Developer\Models\WebhookDelivery;
use App\Domain\Developer\Services\ApiCredentialService;
use App\Domain\Developer\Services\ApiUsageTracker;
use App\Domain\Developer\Services\ApiVersionResolver;
use App\Domain\Developer\Services\RateLimitService;
use App\Domain\Developer\Services\SandboxService;
use App\Domain\Developer\Services\WebhookRetryService;
use App\Domain\Developer\Services\WebhookService;
use App\Domain\Developer\Services\WebhookDeliveryService;
use App\Domain\Reporting\Models\AnalyticsEvent;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeveloperPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_key_generation_and_validation(): void
    {
        $tenant = $this->tenant();
        $client = app(ApiCredentialService::class)->createClient(new ApiClientData('Partner App', $tenant->id));

        $issued = app(ApiCredentialService::class)->issueApiKey(new ApiKeyData($client->id, 'Default Key', $tenant->id));
        $validated = app(ApiCredentialService::class)->validateApiKey($issued['plain_text_key']);

        $this->assertSame($issued['api_key']->id, $validated->id);
        $this->assertNotSame($issued['plain_text_key'], $validated->key_hash);
    }

    public function test_token_validation_rejects_expired_token(): void
    {
        $issued = app(ApiCredentialService::class)->issueToken(new ApiTokenData(
            name: 'Expired PAT',
            expiresAt: now()->subMinute(),
        ));

        $this->expectException(InvalidApiCredentialException::class);
        app(ApiCredentialService::class)->validateToken($issued['plain_text_token']);
    }

    public function test_api_version_resolution(): void
    {
        ApiVersion::query()->create(['version' => 'v1', 'status' => 'active', 'is_default' => true]);

        $version = app(ApiVersionResolver::class)->resolve('v1');

        $this->assertSame('v1', $version->version);
    }

    public function test_invalid_api_version_is_rejected(): void
    {
        $this->expectException(InvalidApiVersionException::class);
        app(ApiVersionResolver::class)->resolve('v99');
    }

    public function test_rate_limiting_rejects_request_above_limit(): void
    {
        $tenant = $this->tenant();
        app(RateLimitService::class)->createRule(new RateLimitRuleData(
            sustainedLimit: 1,
            burstLimit: 1,
            tenantId: $tenant->id,
            endpoint: '/api/v1/test',
        ));

        app(ApiUsageTracker::class)->track(new ApiRequestContext(
            requestId: 'req-1',
            apiVersion: 'v1',
            method: 'GET',
            endpoint: '/api/v1/test',
            tenantId: $tenant->id,
        ));

        $this->expectException(RateLimitExceededException::class);
        app(RateLimitService::class)->assertAllowed(new ApiRequestContext(
            requestId: 'req-2',
            apiVersion: 'v1',
            method: 'GET',
            endpoint: '/api/v1/test',
            tenantId: $tenant->id,
        ));
    }

    public function test_webhook_signature_generation_and_verification(): void
    {
        $payload = '{"event":"payment.succeeded"}';
        $secret = 'whsec_test';
        $signature = app(WebhookService::class)->signPayload($payload, $secret);

        $this->assertTrue(app(WebhookService::class)->verifySignature($payload, $signature, $secret));
    }

    public function test_duplicate_or_invalid_webhook_registration_is_rejected(): void
    {
        $this->expectException(InvalidWebhookException::class);
        app(WebhookService::class)->registerEndpoint(new WebhookEndpointData('http://example.test/webhook', ['payment.succeeded']));
    }

    public function test_webhook_retry_framework_marks_delivery_for_retry(): void
    {
        $registered = app(WebhookService::class)->registerEndpoint(new WebhookEndpointData('https://example.test/webhook', ['payment.succeeded']));
        $event = app(WebhookService::class)->createEvent(new \App\Domain\Developer\DTOs\WebhookEventData(
            eventType: 'payment.succeeded',
            eventReference: 'payment#100',
            payload: ['reference' => 'payment#100'],
        ));

        /** @var WebhookDelivery $delivery */
        $delivery = $event->deliveries()->first();
        app(WebhookRetryService::class)->markForRetry($delivery, 'Timeout');

        $this->assertSame('retrying', $delivery->refresh()->status->value);
        $this->assertNotNull($registered['plain_text_secret']);
    }

    public function test_webhook_delivery_logging_marks_delivery_delivered(): void
    {
        app(WebhookService::class)->registerEndpoint(new WebhookEndpointData('https://example.test/delivered', ['payment.succeeded']));
        $event = app(WebhookService::class)->createEvent(new \App\Domain\Developer\DTOs\WebhookEventData(
            eventType: 'payment.succeeded',
            eventReference: 'payment#delivered',
            payload: ['reference' => 'payment#delivered'],
        ));

        $delivery = app(WebhookDeliveryService::class)->markDelivered($event->deliveries()->first(), 200, 'ok');

        $this->assertSame('delivered', $delivery->status->value);
        $this->assertSame(200, $delivery->response_status);
    }

    public function test_usage_tracking_emits_reporting_analytics_event(): void
    {
        app(ApiUsageTracker::class)->track(new ApiRequestContext(
            requestId: 'req-usage',
            apiVersion: 'v1',
            method: 'POST',
            endpoint: '/api/v1/verifications',
            statusCode: 200,
            latencyMs: 120,
        ));

        $this->assertSame(1, AnalyticsEvent::query()->where('event_reference', 'api#req-usage')->count());
    }

    public function test_sandbox_application_creation_issues_sandbox_key(): void
    {
        $tenant = $this->tenant();

        $sandbox = app(SandboxService::class)->createSandboxApplication('Partner Sandbox', $tenant->id);

        $this->assertSame(DeveloperEnvironment::SANDBOX, $sandbox['application']->environment);
        $this->assertStringStartsWith('ivp_sandbox_', $sandbox['api_key']['plain_text_key']);
    }

    private function tenant(): Tenant
    {
        return Tenant::query()->create([
            'name' => 'Developer Test Tenant',
            'slug' => 'developer-test-'.Str::lower((string) Str::ulid()),
            'status' => 'active',
        ]);
    }
}
