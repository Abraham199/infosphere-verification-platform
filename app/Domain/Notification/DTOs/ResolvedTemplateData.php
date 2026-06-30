<?php

namespace App\Domain\Notification\DTOs;

use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Models\NotificationTemplate;

class ResolvedTemplateData
{
    /**
     * @param array<string, mixed> $variables
     */
    public function __construct(
        public readonly NotificationTemplate $template,
        public readonly NotificationChannel $channel,
        public readonly ?string $subject,
        public readonly ?string $title,
        public readonly string $body,
        public readonly array $variables,
    ) {
    }
}
