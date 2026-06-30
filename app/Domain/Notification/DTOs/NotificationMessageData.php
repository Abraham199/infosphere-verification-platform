<?php

namespace App\Domain\Notification\DTOs;

use App\Domain\Notification\Enums\NotificationCategory;

class NotificationMessageData
{
    /**
     * @param array<int, NotificationRecipientData> $recipients
     * @param array<string, mixed> $variables
     */
    public function __construct(
        public readonly string $eventType,
        public readonly NotificationCategory $category,
        public readonly array $recipients,
        public readonly array $variables = [],
        public readonly ?string $tenantId = null,
        public readonly ?string $userId = null,
        public readonly bool $mandatory = false,
        public readonly string $priority = 'normal',
        public readonly ?string $dedupeKey = null,
    ) {
    }
}
