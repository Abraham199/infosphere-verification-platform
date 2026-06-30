# Analytics Collector

The analytics collector converts domain events into durable reporting facts.

## Collection Rules

- Operational domains emit events.
- `CollectAnalyticsEvent` passes those events to `AnalyticsCollectorInterface`.
- `AnalyticsCollector` maps event name, source domain, tenant, dimensions, measures, and payload.
- Duplicate event references are idempotently ignored.

## Source Domains

- Wallet
- Ledger
- Payment
- Verification
- Product
- Notification
- Tenant

## Data Shape

Analytics facts contain:

- event name
- source domain
- event reference
- tenant
- occurrence time
- dimensions
- measures
- compact payload
