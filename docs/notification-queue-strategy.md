# Notification Queue Strategy

Phase 7 is queue-ready. `notification_deliveries` stores pending deliveries, attempts, retry state, and next retry time.

## Preferred Production Strategy

Use Laravel queue workers for `DeliverNotificationJob` once the hosting environment supports long-running workers or a managed queue.

Recommended worker behavior:

- claim pending deliveries
- call `NotificationService::deliver`
- call `NotificationService::retry` for failed recoverable deliveries
- stop retrying after `max_attempts`
- keep all provider responses in `notification_channel_logs`

## Shared Hosting Fallback

WhoGoHost shared Linux hosting may not support long-running workers. In that case, use a cron command every minute to process due pending and retryable deliveries in small batches.

The cron command should:

- process only `pending` or `retrying` deliveries
- respect `next_retry_at`
- limit batch size to avoid request timeouts
- write delivery logs for every attempt
- avoid duplicate sends by locking rows or using an atomic status transition
