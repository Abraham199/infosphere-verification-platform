<?php

namespace App\Domain\Developer\DTOs;

use Illuminate\Support\Carbon;

class WebhookEventData
{
    public function __construct(
        public readonly string $eventType,
        public readonly string $eventReference,
        public readonly array $payload,
        public readonly ?string $tenantId = null,
        public readonly ?Carbon $occurredAt = null,
    ) {
    }
}
