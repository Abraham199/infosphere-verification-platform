<?php

namespace App\Domain\Developer\Services;

use App\Domain\Developer\Enums\WebhookDeliveryStatus;
use App\Domain\Developer\Events\WebhookDelivered;
use App\Domain\Developer\Events\WebhookFailed;
use App\Domain\Developer\Models\WebhookDelivery;

class WebhookDeliveryService
{
    public function markDelivered(WebhookDelivery $delivery, int $responseStatus, ?string $responseBody = null): WebhookDelivery
    {
        $delivery->forceFill([
            'status' => WebhookDeliveryStatus::DELIVERED,
            'attempts' => $delivery->attempts + 1,
            'response_status' => $responseStatus,
            'response_body' => $responseBody,
            'delivered_at' => now(),
            'failure_reason' => null,
        ])->save();

        $delivery->endpoint?->forceFill(['last_success_at' => now()])->save();

        event(new WebhookDelivered($delivery));

        return $delivery;
    }

    public function markFailed(WebhookDelivery $delivery, ?string $reason = null, ?int $responseStatus = null): WebhookDelivery
    {
        $delivery->forceFill([
            'status' => WebhookDeliveryStatus::FAILED,
            'attempts' => $delivery->attempts + 1,
            'response_status' => $responseStatus,
            'failure_reason' => $reason,
        ])->save();

        $delivery->endpoint?->forceFill(['last_failure_at' => now()])->save();

        event(new WebhookFailed($delivery));

        return $delivery;
    }
}
