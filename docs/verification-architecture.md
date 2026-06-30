# Verification Architecture

## Scope

Phase 5 implements the provider-agnostic Verification Platform. It does not implement notifications, reports, dashboard UI, support, affiliate, promotions, public API, marketplace services, or additional providers beyond SwiftVerify.

## Flow

```text
Customer / Tenant
  -> Verification Service
  -> Verification Provider Manager
  -> Verification Provider Adapter
  -> SwiftVerify
  -> Provider Response
  -> Verification Service
  -> Wallet Reservation / Debit
  -> Ledger Posting
  -> Domain Events
  -> Notifications later
```

## Boundaries

- Verification owns service catalog, pricing resolution, provider selection, provider logs, status transitions, and verification results.
- Wallet is accessed only through wallet service interfaces.
- Ledger is accessed only through ledger interfaces.
- SwiftVerify is hidden behind a provider adapter.
- Notifications are not implemented and will listen to domain events later.

## Tables

- `verification_services`
- `provider_services`
- `verification_pricing_rules`
- `verification_requests`
- `verification_results`
- `verification_provider_logs`

`verification_pricing_rules` was added because tenant-specific pricing is a Phase 5 requirement and must remain historically auditable.
