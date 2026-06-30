<?php

namespace App\Domain\Notification\Interfaces;

use App\Domain\Notification\DTOs\ResolvedTemplateData;
use App\Domain\Notification\Enums\NotificationChannel;

interface NotificationTemplateResolverInterface
{
    /**
     * @param array<string, mixed> $variables
     */
    public function resolve(?string $tenantId, string $eventType, NotificationChannel $channel, array $variables = []): ResolvedTemplateData;
}
