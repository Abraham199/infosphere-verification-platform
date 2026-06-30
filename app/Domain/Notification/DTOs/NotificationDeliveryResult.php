<?php

namespace App\Domain\Notification\DTOs;

class NotificationDeliveryResult
{
    /**
     * @param array<string, mixed> $response
     */
    public function __construct(
        public readonly bool $successful,
        public readonly ?string $providerMessageId = null,
        public readonly ?string $failureReason = null,
        public readonly array $response = [],
    ) {
    }
}
