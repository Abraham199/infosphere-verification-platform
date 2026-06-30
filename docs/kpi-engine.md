# KPI Engine

The KPI engine calculates metrics from the reporting store, not from operational modules.

## Supported KPI Families

- Financial: revenue, wallet credits, wallet debits, wallet balance.
- Verification: successful checks, failed checks, provider success rate, processing time.
- Payments: payment success rate, failed payments, pending payments, refund volume.
- Products: product revenue, usage, top-selling product placeholders.
- Tenants: active tenant and growth metrics.
- System: notification volume, queue statistics, future API usage.

## Extension Rule

New KPIs should be added through `MetricKey` and `KPIEngine` without changing operational domains. If a KPI needs new data, the source domain should emit richer events and the analytics collector should map the new dimensions or measures.
