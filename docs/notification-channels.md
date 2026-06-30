# Notification Channels

## Implemented Channel

### Email

Email delivery is implemented by `App\Infrastructure\Notifications\Email\EmailChannelAdapter`. It uses Laravel Mail and remains behind `NotificationChannelAdapterInterface`.

## Prepared Channels

- SMS
- WhatsApp
- In-App
- Push

These channels are available in `NotificationChannel` and resolved through `NotificationChannelManagerInterface`, but delivery adapters are not implemented in Phase 7.

## Adapter Rules

- Every adapter must accept a `NotificationDelivery`.
- Every adapter must return `NotificationDeliveryResult`.
- Adapters must not update wallet, ledger, payment, verification, or product state.
- Provider request and response details must be written through channel logs.
