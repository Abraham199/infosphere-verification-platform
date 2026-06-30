<?php

namespace App\Domain\Developer\Interfaces;

use App\Domain\Developer\DTOs\WebhookEndpointData;
use App\Domain\Developer\DTOs\WebhookEventData;
use App\Domain\Developer\Models\WebhookEndpoint;
use App\Domain\Developer\Models\WebhookEvent;

interface WebhookServiceInterface
{
    public function registerEndpoint(WebhookEndpointData $data): array;
    public function createEvent(WebhookEventData $data): WebhookEvent;
    public function signPayload(string $payload, string $secret): string;
    public function verifySignature(string $payload, string $signature, string $secret): bool;
}
