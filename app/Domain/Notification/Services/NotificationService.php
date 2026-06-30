<?php

namespace App\Domain\Notification\Services;

use App\Domain\Notification\DTOs\NotificationMessageData;
use App\Domain\Notification\Enums\NotificationDeliveryStatus;
use App\Domain\Notification\Enums\NotificationStatus;
use App\Domain\Notification\Events\NotificationFailed;
use App\Domain\Notification\Events\NotificationQueued;
use App\Domain\Notification\Events\NotificationRetried;
use App\Domain\Notification\Events\NotificationSent;
use App\Domain\Notification\Interfaces\NotificationChannelManagerInterface;
use App\Domain\Notification\Interfaces\NotificationRepositoryInterface;
use App\Domain\Notification\Interfaces\NotificationServiceInterface;
use App\Domain\Notification\Interfaces\NotificationTemplateResolverInterface;
use App\Domain\Notification\Models\Notification;
use App\Domain\Notification\Models\NotificationDelivery;
use App\Domain\Notification\Validators\NotificationValidationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
        private readonly NotificationTemplateResolverInterface $templates,
        private readonly NotificationChannelManagerInterface $channels,
        private readonly NotificationPreferenceService $preferences,
        private readonly NotificationValidationService $validator,
    ) {
    }

    public function queue(NotificationMessageData $message): Notification
    {
        $this->validator->validateMessage($message);

        return DB::transaction(function () use ($message): Notification {
            $firstRecipient = $message->recipients[0];
            $resolved = $this->templates->resolve($message->tenantId, $message->eventType, $firstRecipient->channel, $message->variables);

            $notification = $this->notifications->createNotification([
                'tenant_id' => $message->tenantId,
                'user_id' => $message->userId,
                'event_type' => $message->eventType,
                'category' => $message->category,
                'priority' => $message->priority,
                'status' => NotificationStatus::QUEUED,
                'subject' => $resolved->subject,
                'title' => $resolved->title,
                'body' => $resolved->body,
                'data' => $message->variables,
                'queued_at' => now(),
            ]);

            foreach ($message->recipients as $recipient) {
                if (! $this->preferences->isAllowed($message->tenantId, $message->userId, $message->eventType, $recipient->channel, $message->category)) {
                    continue;
                }

                $reference = $message->dedupeKey !== null
                    ? $message->dedupeKey.'-'.$recipient->channel->value.'-'.substr(sha1($recipient->address), 0, 12)
                    : 'NTF-'.strtoupper((string) Str::ulid());
                $this->validator->ensureDeliveryReferenceIsUnique($reference);

                $delivery = $this->notifications->createDelivery([
                    'notification_id' => $notification->id,
                    'tenant_id' => $message->tenantId,
                    'user_id' => $message->userId,
                    'channel' => $recipient->channel,
                    'recipient' => $recipient->address,
                    'delivery_reference' => $reference,
                    'status' => NotificationDeliveryStatus::PENDING,
                    'metadata' => ['template_id' => $resolved->template->id],
                ]);

                event(new NotificationQueued($delivery));
            }

            return $notification;
        });
    }

    public function deliver(NotificationDelivery $delivery): NotificationDelivery
    {
        $startedAt = microtime(true);

        try {
            $result = $this->channels->driver($delivery->channel)->send($delivery);

            $delivery->forceFill([
                'attempts' => $delivery->attempts + 1,
                'status' => $result->successful ? NotificationDeliveryStatus::SENT : NotificationDeliveryStatus::FAILED,
                'sent_at' => $result->successful ? now() : null,
                'failed_at' => $result->successful ? null : now(),
                'provider_message_id' => $result->providerMessageId,
                'failure_reason' => $result->failureReason,
            ])->save();

            $this->notifications->createChannelLog([
                'notification_delivery_id' => $delivery->id,
                'tenant_id' => $delivery->tenant_id,
                'channel' => $delivery->channel,
                'status' => $delivery->status->value,
                'request_payload' => ['recipient' => $delivery->recipient, 'reference' => $delivery->delivery_reference],
                'response_payload' => $result->response,
                'error_message' => $result->failureReason,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            event($result->successful ? new NotificationSent($delivery) : new NotificationFailed($delivery));
            $this->syncNotificationStatus($delivery);

            return $delivery;
        } catch (Throwable $exception) {
            $delivery->forceFill([
                'attempts' => $delivery->attempts + 1,
                'status' => NotificationDeliveryStatus::FAILED->value,
                'failed_at' => now(),
                'failure_reason' => $exception->getMessage(),
            ])->save();

            $this->notifications->createChannelLog([
                'notification_delivery_id' => $delivery->id,
                'tenant_id' => $delivery->tenant_id,
                'channel' => $delivery->channel,
                'status' => NotificationDeliveryStatus::FAILED,
                'request_payload' => ['recipient' => $delivery->recipient, 'reference' => $delivery->delivery_reference],
                'error_message' => $exception->getMessage(),
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            event(new NotificationFailed($delivery));
            $this->syncNotificationStatus($delivery);

            return $delivery;
        }
    }

    public function retry(NotificationDelivery $delivery): NotificationDelivery
    {
        if ($delivery->attempts >= $delivery->max_attempts) {
            return $delivery;
        }

        $delivery->forceFill([
            'status' => NotificationDeliveryStatus::RETRYING,
            'next_retry_at' => now()->addMinutes(5 * max(1, $delivery->attempts)),
        ])->save();

        event(new NotificationRetried($delivery));

        return $delivery;
    }

    private function syncNotificationStatus(NotificationDelivery $delivery): void
    {
        $notification = $delivery->notification()->first();

        if ($notification === null) {
            return;
        }

        $statuses = $notification->deliveries()
            ->pluck('status')
            ->map(fn (string|NotificationDeliveryStatus $status): string => $status instanceof NotificationDeliveryStatus ? $status->value : $status)
            ->all();

        if ($statuses !== [] && collect($statuses)->every(fn (string $status): bool => $status === NotificationDeliveryStatus::SENT->value)) {
            $notification->forceFill([
                'status' => NotificationStatus::SENT,
                'sent_at' => now(),
            ])->save();

            return;
        }

        if ($statuses !== [] && collect($statuses)->every(fn (string $status): bool => in_array($status, [
            NotificationDeliveryStatus::FAILED->value,
            NotificationDeliveryStatus::CANCELLED->value,
        ], true))) {
            $notification->forceFill(['status' => NotificationStatus::FAILED])->save();
        }
    }
}
