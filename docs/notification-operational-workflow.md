# Notification Operational Workflow

Sprint 2 Milestone 5 exposes the existing Notification Engine through platform and tenant Blade workflows.

## Platform Notification Center

Platform notification pages are available under `/platform/notifications` and remain protected by the existing `Super Admin` platform route boundary.

Supported platform operations:

- notification dashboard with delivery counts
- notification queue filtering by type, status, and channel
- failed delivery list
- delivery detail view with channel logs
- notification detail view with delivery status
- retry workflow through `NotificationServiceInterface::retry`

The platform workflow does not send notifications directly from controllers. Controllers delegate to `NotificationCenterWorkflowService`, which uses the existing Notification Engine services and models.

## Tenant Notification Center

Tenant notification pages are available under `/t/{tenant}/notifications`.

Required permissions:

- `notifications.view`
- `notifications.preferences`

Supported tenant operations:

- inbox
- unread notifications
- read notifications
- archived notifications
- mark as read
- archive notification
- notification preferences

Tenant reads are scoped to the resolved tenant context and the current user. Shared tenant notifications with no `user_id` are also visible to tenant users. Archive state is stored in the existing `notifications.data` JSON payload to avoid unnecessary schema changes.

## Notification Types

The operational center recognizes these event types:

- `ticket.created`
- `ticket.assigned`
- `ticket.updated`
- `ticket.closed`
- `wallet.funded`
- `wallet.debited`
- `payment.successful`
- `payment.failed`
- `verification.started`
- `verification.completed`
- `verification.failed`
- `product.activated`
- `product.suspended`

## Channels

Implemented and prepared channels are represented through the existing `NotificationChannel` enum.

- In-App: represented by persisted notification records in the tenant inbox
- Email: delivered through the existing email channel adapter
- SMS: preference and filter placeholder
- Webhook: represented by delivery filtering and future developer-platform integration

## Boundary Rules

- Existing Notification Engine services own queueing, delivery, retry, template resolution, and preference enforcement.
- Controllers remain thin and only delegate to `NotificationCenterWorkflowService`.
- Blade views are presentation-only.
- No notification tables were added.
- Tenant isolation and RBAC are enforced at route and service-query level.
