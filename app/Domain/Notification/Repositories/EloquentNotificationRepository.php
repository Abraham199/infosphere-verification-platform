<?php

namespace App\Domain\Notification\Repositories;

use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Enums\NotificationTemplateStatus;
use App\Domain\Notification\Interfaces\NotificationRepositoryInterface;
use App\Domain\Notification\Models\Notification;
use App\Domain\Notification\Models\NotificationChannelLog;
use App\Domain\Notification\Models\NotificationDelivery;
use App\Domain\Notification\Models\NotificationPreference;
use App\Domain\Notification\Models\NotificationTemplate;

class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    public function findTemplate(?string $tenantId, string $eventType, NotificationChannel $channel): ?NotificationTemplate
    {
        if ($tenantId === null) {
            return null;
        }

        return NotificationTemplate::query()
            ->where('tenant_id', $tenantId)
            ->where('event_type', $eventType)
            ->where('channel', $channel->value)
            ->where('status', NotificationTemplateStatus::ACTIVE->value)
            ->orderByDesc('version')
            ->first();
    }

    public function findGlobalTemplate(string $eventType, NotificationChannel $channel): ?NotificationTemplate
    {
        return NotificationTemplate::query()
            ->whereNull('tenant_id')
            ->where('event_type', $eventType)
            ->where('channel', $channel->value)
            ->where('status', NotificationTemplateStatus::ACTIVE->value)
            ->orderByDesc('is_default')
            ->orderByDesc('version')
            ->first();
    }

    public function findPreference(?string $tenantId, ?string $userId, string $eventType, NotificationChannel $channel): ?NotificationPreference
    {
        return NotificationPreference::query()
            ->where('notification_type', $eventType)
            ->where('channel', $channel->value)
            ->where(function ($query) use ($tenantId, $userId): void {
                $query->where(function ($query) use ($tenantId, $userId): void {
                    $query->where('tenant_id', $tenantId)->where('user_id', $userId);
                })->orWhere(function ($query) use ($tenantId): void {
                    $query->where('tenant_id', $tenantId)->whereNull('user_id');
                })->orWhere(function ($query): void {
                    $query->whereNull('tenant_id')->whereNull('user_id');
                });
            })
            ->orderByRaw('CASE WHEN user_id IS NOT NULL THEN 1 ELSE 0 END DESC')
            ->orderByRaw('CASE WHEN tenant_id IS NOT NULL THEN 1 ELSE 0 END DESC')
            ->first();
    }

    public function deliveryReferenceExists(string $reference): bool
    {
        return NotificationDelivery::query()->where('delivery_reference', $reference)->exists();
    }

    public function createNotification(array $attributes): Notification
    {
        return Notification::query()->create($attributes);
    }

    public function createDelivery(array $attributes): NotificationDelivery
    {
        return NotificationDelivery::query()->create($attributes);
    }

    public function createChannelLog(array $attributes): void
    {
        NotificationChannelLog::query()->create($attributes);
    }
}
