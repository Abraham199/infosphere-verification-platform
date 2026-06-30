# Verification UI Workflow

## Scope

Sprint 2 Milestone 2 turns the tenant verification UI into an operational workflow while preserving the existing backend architecture.

## Routes

Tenant verification routes live under:

`/t/{tenant:slug}/verification`

Routes:

- `GET /verification`
- `GET /verification/services`
- `POST /verification/request`
- `GET /verification/history`
- `GET /verification/result/{reference}`
- `GET /verification/{reference}`

All routes use existing `auth`, `verified`, `tenant`, and `permission:dashboard.view` middleware.

## Pages

### Request Page

Shows:

- Service selector
- Subject identifier field
- Optional customer context fields
- Pricing preview
- Wallet readiness card
- Recent request table

### Services Page

Shows active verification services with tenant-resolved pricing.

### History Page

Provides tenant-scoped search and status filtering.

### Status Page

Shows request status, wallet reservation details, and wallet transaction details.

### Result Page

Shows result status, confidence score, summary, and normalized data if available.

## Boundaries

- Controllers create DTOs and call `VerificationServiceInterface`.
- Controllers do not call SwiftVerify or any provider adapter.
- Wallet reservation, provider submission, wallet debit, reservation release, ledger posting, events, and results remain inside the Verification Platform.
- Blade views remain presentation-only.
- No new tables were added.
