<?php

namespace App\Domain\Notification\Interfaces;

use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Models\Notification;
use App\Domain\Notification\Models\NotificationDelivery;
use App\Domain\Notification\Models\NotificationPreference;
use App\Domain\Notification\Models\NotificationTemplate;

interface NotificationRepositoryInterface
{
    public function findTemplate(?string $tenantId, string $eventType, NotificationChannel $channel): ?NotificationTemplate;
    public function findGlobalTemplate(string $eventType, NotificationChannel $channel): ?NotificationTemplate;
    public function findPreference(?string $tenantId, ?string $userId, string $eventType, NotificationChannel $channel): ?NotificationPreference;
    public function deliveryReferenceExists(string $reference): bool;
    public function createNotification(array $attributes): Notification;
    public function createDelivery(array $attributes): NotificationDelivery;
    public function createChannelLog(array $attributes): void;
}
