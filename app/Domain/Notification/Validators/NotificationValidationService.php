<?php

namespace App\Domain\Notification\Validators;

use App\Domain\Notification\DTOs\NotificationMessageData;
use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Exceptions\DuplicateDeliveryReferenceException;
use App\Domain\Notification\Exceptions\InvalidNotificationChannelException;
use App\Domain\Notification\Exceptions\NotificationException;
use App\Domain\Notification\Interfaces\NotificationRepositoryInterface;

class NotificationValidationService
{
    public function __construct(private readonly NotificationRepositoryInterface $notifications)
    {
    }

    public function validateMessage(NotificationMessageData $message): void
    {
        if ($message->recipients === []) {
            throw new NotificationException('Notification recipient is required.');
        }

        foreach ($message->recipients as $recipient) {
            if (! $recipient->channel instanceof NotificationChannel) {
                throw new InvalidNotificationChannelException('Invalid notification channel.');
            }

            if (trim($recipient->address) === '') {
                throw new NotificationException('Notification recipient address is required.');
            }

            if ($recipient->channel === NotificationChannel::EMAIL && filter_var($recipient->address, FILTER_VALIDATE_EMAIL) === false) {
                throw new NotificationException('Notification email recipient is invalid.');
            }
        }
    }

    public function ensureDeliveryReferenceIsUnique(string $reference): void
    {
        if ($this->notifications->deliveryReferenceExists($reference)) {
            throw new DuplicateDeliveryReferenceException('Duplicate notification delivery reference.');
        }
    }

    /**
     * @param array<int, string> $requiredVariables
     * @param array<string, mixed> $providedVariables
     */
    public function validateVariables(array $requiredVariables, array $providedVariables): void
    {
        foreach ($requiredVariables as $variable) {
            if (! array_key_exists($variable, $providedVariables)) {
                throw new NotificationException("Missing notification template variable [{$variable}].");
            }
        }
    }
}
