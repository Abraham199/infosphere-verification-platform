<?php

namespace App\Domain\Developer\Services;

use App\Domain\Developer\DTOs\WebhookEndpointData;
use App\Domain\Developer\DTOs\WebhookEventData;
use App\Domain\Developer\Enums\WebhookDeliveryStatus;
use App\Domain\Developer\Enums\WebhookStatus;
use App\Domain\Developer\Exceptions\InvalidWebhookException;
use App\Domain\Developer\Interfaces\DeveloperRepositoryInterface;
use App\Domain\Developer\Interfaces\WebhookServiceInterface;
use App\Domain\Developer\Models\WebhookEndpoint;
use App\Domain\Developer\Models\WebhookEvent;
use App\Domain\Developer\Validators\DeveloperValidationService;
use Illuminate\Support\Str;

class WebhookService implements WebhookServiceInterface
{
    public function __construct(
        private readonly DeveloperRepositoryInterface $developers,
        private readonly DeveloperValidationService $validator,
    ) {
    }

    public function registerEndpoint(WebhookEndpointData $data): array
    {
        $this->validator->validateWebhookEndpoint($data);

        $secret = 'whsec_'.Str::random(48);
        $endpoint = $this->developers->createWebhookEndpoint([
            'tenant_id' => $data->tenantId,
            'api_client_id' => $data->apiClientId,
            'environment' => $data->environment,
            'url' => $data->url,
            'secret_hash' => hash('sha256', $secret),
            'subscribed_events' => $data->events,
            'status' => WebhookStatus::ACTIVE,
        ]);

        return ['endpoint' => $endpoint, 'plain_text_secret' => $secret];
    }

    public function createEvent(WebhookEventData $data): WebhookEvent
    {
        $event = $this->developers->createWebhookEvent([
            'tenant_id' => $data->tenantId,
            'event_type' => $data->eventType,
            'event_reference' => $data->eventReference,
            'payload' => $data->payload,
            'occurred_at' => $data->occurredAt ?? now(),
        ]);

        $environment = str_starts_with($data->eventType, 'sandbox.') ? 'sandbox' : 'production';
        foreach ($this->developers->subscribedEndpoints($data->eventType, $data->tenantId, $environment) as $endpoint) {
            $this->developers->createWebhookDelivery([
                'webhook_event_id' => $event->id,
                'webhook_endpoint_id' => $endpoint->id,
                'delivery_reference' => 'whd_'.Str::lower((string) Str::ulid()),
                'status' => WebhookDeliveryStatus::PENDING,
                'next_retry_at' => now(),
            ]);
        }

        return $event;
    }

    public function signPayload(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }

    public function verifySignature(string $payload, string $signature, string $secret): bool
    {
        if ($signature === '') {
            throw new InvalidWebhookException('Webhook signature is required.');
        }

        return hash_equals($this->signPayload($payload, $secret), $signature);
    }
}
