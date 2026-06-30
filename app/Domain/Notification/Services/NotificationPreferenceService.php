<?php

namespace App\Domain\Notification\Services;

use App\Domain\Notification\Enums\NotificationCategory;
use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Events\NotificationPreferenceUpdated;
use App\Domain\Notification\Models\NotificationPreference;

class NotificationPreferenceService
{
    public function isAllowed(?string $tenantId, ?string $userId, string $eventType, NotificationChannel $channel, NotificationCategory $category): bool
    {
        if ($category->isMandatory()) {
            return true;
        }

        $preference = NotificationPreference::query()
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

        return $preference?->is_enabled ?? true;
    }

    public function update(NotificationPreference $preference, bool $enabled): NotificationPreference
    {
        if (($preference->category?->isMandatory() ?? false) || $preference->is_mandatory) {
            $enabled = true;
        }

        $preference->forceFill(['is_enabled' => $enabled])->save();

        event(new NotificationPreferenceUpdated($preference));

        return $preference;
    }
}
