# Report Generation

Reports are generated as `reporting_snapshots`.

## Current Framework

`ReportGenerator` accepts a `ReportRequestData`, validates the date range, calculates the report's KPIs, and stores a snapshot with metric values and parameters.

## Report Keys

- `financial_summary`
- `verification_summary`
- `payment_summary`
- `product_summary`
- `tenant_summary`
- `system_summary`

Unknown report keys fall back to a compact general platform summary. Future phases can replace this with configurable report definitions.

## Scheduled Reports

`ScheduledReportService` creates `reporting_jobs` records for daily, weekly, and monthly reports. Execution is queue-ready through `GenerateReportJob`, while actual dashboard UI and delivery are reserved for later phases.
