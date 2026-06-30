<?php

namespace App\Infrastructure\Notifications\Email;

use App\Domain\Notification\DTOs\NotificationDeliveryResult;
use App\Domain\Notification\Interfaces\NotificationChannelAdapterInterface;
use App\Domain\Notification\Models\NotificationDelivery;
use Illuminate\Support\Facades\Mail;

class EmailChannelAdapter implements NotificationChannelAdapterInterface
{
    public function send(NotificationDelivery $delivery): NotificationDeliveryResult
    {
        $notification = $delivery->notification;

        Mail::raw($notification->body, function ($message) use ($delivery, $notification): void {
            $message->to($delivery->recipient)
                ->subject($notification->subject ?? $notification->title ?? 'Notification');
        });

        return new NotificationDeliveryResult(
            successful: true,
            providerMessageId: $delivery->delivery_reference,
            response: ['mailer' => config('mail.default'), 'reference' => $delivery->delivery_reference],
        );
    }
}
