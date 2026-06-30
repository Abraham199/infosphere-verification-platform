<?php

namespace App\Domain\Developer\Services;

use App\Domain\Developer\Enums\WebhookDeliveryStatus;
use App\Domain\Developer\Events\WebhookFailed;
use App\Domain\Developer\Models\WebhookDelivery;

class WebhookRetryService
{
    public function markForRetry(WebhookDelivery $delivery, ?string $reason = null): WebhookDelivery
    {
        if ($delivery->attempts >= $delivery->max_attempts) {
            $delivery->forceFill([
                'status' => WebhookDeliveryStatus::FAILED,
                'failure_reason' => $reason,
            ])->save();

            event(new WebhookFailed($delivery));

            return $delivery;
        }

        $delivery->forceFill([
            'status' => WebhookDeliveryStatus::RETRYING,
            'attempts' => $delivery->attempts + 1,
            'next_retry_at' => now()->addMinutes(2 ** max(0, $delivery->attempts)),
            'failure_reason' => $reason,
        ])->save();

        return $delivery;
    }
}
