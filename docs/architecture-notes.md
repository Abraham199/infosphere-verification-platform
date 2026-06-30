# Architecture Notes

## Phase 1 Scope

This phase implements only the enterprise foundation:

- Laravel application skeleton.
- Authentication flow.
- Tenant model and resolution.
- Tenant-aware RBAC.
- Feature flag infrastructure.
- Base services, repositories, contracts, middleware, policies, and events.

Ledger, Paystack, SwiftVerify, verification, reporting, monitoring, notifications, affiliate, promotions, support, and developer API implementations are intentionally deferred.

## Phase 2 Wallet Domain

The wallet domain implements operational wallet balances, wallet accounts, wallet transactions, reservations, adjustments, domain events, validation, and row-level locking. It intentionally excludes the future ledger engine and all external payment or verification integrations.

## Phase 3 Ledger Engine

The ledger engine implements double-entry accounting, ledger accounts, ledger batches, ledger entries, reversals, balance queries, and reconciliation scaffolding. It intentionally excludes provider integrations, reporting dashboards, notification delivery, and public APIs.

## Phase 4 Payment Platform

The payment platform implements provider-agnostic payment initialization, provider verification, Paystack adapter, secure webhook verification, idempotent processing, wallet crediting, ledger posting, payment state transitions, and payment events. It intentionally excludes UI, public API endpoints, notifications, reporting, verification, and non-payment business modules.

## Phase 5 Verification Platform

The verification platform implements provider-agnostic verification services, service catalog, provider mappings, tenant-aware pricing, SwiftVerify adapter, wallet reservation/debit, ledger posting, provider logs, status transitions, and domain events. It intentionally excludes notifications, reporting, dashboard UI, public API, support, affiliate, promotions, and marketplace services.

## Phase 6 Product Engine

The Product Engine implements configurable product categories, products, provider mappings, tenant product availability, product features, capabilities, lifecycle rules, and product domain events. It intentionally excludes product purchase flows, provider transaction execution, dashboard UI, public API, marketplace transactions, and notification listeners.

## Tenancy

The platform uses application-level tenancy in a shared database. Tenant-owned records must include `tenant_id`, and tenant context is resolved before tenant routes are accessed.

## Subscription Readiness

Subscription and billing are represented by contracts and feature flags. Full billing workflows will be added in a later phase without changing the tenant core.

## External Integrations

All external providers must be implemented behind contracts and adapters. Phase 1 includes contracts for payment, verification, notification, and subscription billing without activating those modules.
