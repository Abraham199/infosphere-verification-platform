# Project Architecture Manifest

## Infosphere Verification Portal

Product: Infosphere Verification Portal  
Company: Info-Sphere Technologies  
Architecture: Modular Laravel Monolith  
Tenancy: Application-level multi-tenancy with shared database  
Status: Technical blueprint after Phase 5  

This manifest documents the current system architecture and the rules future phases must follow. It is intended to protect module boundaries, prevent accidental coupling, and keep IVP scalable as it grows into a broader digital services platform.

---

## 1. Architecture Principles

IVP is built as a modular Laravel monolith. Each domain owns its data model, business rules, services, events, and validation logic.

Core principles:

- Business logic belongs in domain services, not controllers or models.
- Models define relationships and persistence shape only.
- External providers are accessed through contracts and adapters.
- Wallet owns operational balances.
- Ledger owns accounting truth.
- Payment owns payment provider interaction.
- Verification owns service catalog, pricing, provider routing, and verification lifecycle.
- Events announce completed business actions but do not replace transactional consistency.
- Future modules must integrate through contracts and services, not direct database manipulation.

---

## 2. Current Domains and Modules

### 2.1 Tenancy Domain

Purpose:

- Represents tenant organizations.
- Resolves tenant context.
- Supports tenant-aware access and settings.

Key classes:

- `Tenant`
- `TenantBranding`
- `TenantDomain`
- `TenantSetting`
- `TenantContext`
- `TenantResolver`

Owned tables:

- `tenants`
- `tenant_branding`
- `tenant_domains`
- `tenant_settings`

Allowed dependencies:

- May be used by all tenant-aware modules.
- Must not depend on Wallet, Ledger, Payment, or Verification.

### 2.2 Identity and Access Domain

Purpose:

- Authentication.
- Roles and permissions.
- Platform and tenant user access.

Key elements:

- `User`
- `Role`
- `Permission`
- Policies
- Spatie permission integration

Owned tables:

- `users`
- `roles`
- `permissions`
- `user_roles`
- `role_permissions`
- `model_has_permissions`

Allowed dependencies:

- May depend on Tenancy.
- Must not depend on business domains.

### 2.3 Wallet Domain

Purpose:

- Operational wallet balances.
- Credits, debits, reservations, refunds, freezes, adjustments.
- Prevents double spending through transactions and row locks.

Key services:

- `WalletCreationService`
- `WalletBalanceService`
- `WalletCreditService`
- `WalletDebitService`
- `WalletReservationService`
- `WalletRefundService`
- `WalletFreezeService`
- `WalletAdjustmentService`
- `WalletValidationService`

Owned tables:

- `wallets`
- `wallet_accounts`
- `wallet_transactions`
- `wallet_reservations`
- `wallet_adjustments`

Allowed dependencies:

- May depend on Tenancy.
- May expose interfaces for Payment and Verification.
- May post to Ledger only through `WalletLedgerPostingInterface`.
- Must never depend on Paystack, SwiftVerify, controllers, UI, or provider-specific code.

### 2.4 Ledger Domain

Purpose:

- Single source of truth for accounting history.
- Double-entry accounting.
- Append-only posted entries.
- Reversals and reconciliation framework.

Key services:

- `LedgerPostingService`
- `LedgerBalanceService`
- `LedgerValidationService`
- `LedgerReversalService`
- `LedgerReconciliationService`
- `LedgerBatchService`
- `LedgerQueryService`

Owned tables:

- `ledger_accounts`
- `ledger_batches`
- `ledger_entries`
- `reconciliation_runs`
- `reconciliation_results`

Allowed dependencies:

- May depend on Tenancy.
- Must not depend on Wallet, Payment, Verification, Paystack, or SwiftVerify.
- Other modules may post to Ledger through interfaces only.

### 2.5 Payment Domain

Purpose:

- Provider-agnostic payment lifecycle.
- Payment initialization.
- Provider verification.
- Webhook processing.
- Idempotent wallet funding.
- Ledger posting after wallet credit.

Key services:

- `PaymentService`
- `PaymentProviderManager`
- `PaymentStateMachine`
- `PaymentValidationService`

Provider adapter:

- `PaystackAdapter`

Owned tables:

- `payment_transactions`
- `payment_attempts`
- `payment_webhooks`
- `payment_provider_logs`

Allowed dependencies:

- May depend on Wallet interfaces/services.
- May depend on Ledger interfaces.
- May depend on provider contracts.
- Must not let Wallet call Paystack directly.
- Must not implement notifications directly.

### 2.6 Verification Domain

Purpose:

- Provider-agnostic verification lifecycle.
- Service catalog.
- Provider service mapping.
- Tenant-aware pricing.
- Wallet reservation/debit.
- Ledger posting after successful verification charge.

Key services:

- `VerificationService`
- `VerificationPricingService`
- `VerificationProviderManager`
- `VerificationStateMachine`
- `VerificationValidationService`

Provider adapter:

- `SwiftVerifyAdapter`

Owned tables:

- `verification_services`
- `provider_services`
- `verification_pricing_rules`
- `verification_requests`
- `verification_results`
- `verification_provider_logs`

Allowed dependencies:

- May depend on Wallet interfaces/services.
- May depend on Ledger interfaces.
- May depend on provider contracts.
- Must not let Wallet or Ledger communicate with SwiftVerify.
- Must not implement notifications directly.

### 2.7 Product Domain

Purpose:

- Central catalog of sellable products and services.
- Configurable categories, product identity, lifecycle, provider mappings, tenant availability, features, and capabilities.

Owned tables:

- `product_categories`
- `products`
- `product_provider_mappings`
- `tenant_products`
- `product_features`
- `product_capabilities`

Allowed dependencies:

- May depend on Tenancy for tenant availability.
- Must not execute provider transactions.
- Must not mutate Wallet or Ledger.
- Business modules may read product definitions and capabilities before executing their own workflows.

---

## 3. Allowed Dependency Direction

Dependencies should flow inward toward stable domain contracts.

Allowed:

```text
Payment -> Wallet interfaces
Payment -> Ledger interfaces
Payment -> Payment provider contracts

Verification -> Wallet interfaces
Verification -> Ledger interfaces
Verification -> Verification provider contracts

Wallet -> Ledger posting bridge interface

All tenant-aware modules -> Tenancy context
```

Not allowed:

```text
Wallet -> Paystack
Wallet -> SwiftVerify
Ledger -> Wallet
Ledger -> Payment
Ledger -> Verification
Payment -> Verification
Verification -> Payment
Provider adapters -> Controllers or UI
Controllers -> Direct balance mutation
Controllers -> Direct ledger posting
```

Future modules must follow the same rule:

```text
Future Service Module
  -> Wallet interfaces
  -> Ledger interfaces
  -> Provider contract
  -> Events
```

---

## 4. Module Communication

### 4.1 Synchronous Service Calls

Use synchronous service calls when a business action must be transactionally consistent.

Examples:

- Payment verification credits wallet.
- Wallet credit posts ledger entry.
- Verification success debits wallet.
- Verification charge posts ledger entry.

### 4.2 Domain Events

Use domain events to announce completed actions and allow future listeners.

Events must not be required to complete core financial consistency.

Examples:

- `PaymentSucceeded`
- `PaymentWalletCredited`
- `VerificationCompleted`
- `WalletCredited`
- `LedgerBatchCompleted`

Future notification listeners can subscribe to these events without changing the core services.

### 4.3 Provider Adapters

Provider adapters translate IVP requests into provider requests and provider responses into IVP response DTOs.

Adapters must:

- Use config-based credentials.
- Avoid hardcoded secrets.
- Normalize responses.
- Apply timeout rules.
- Verify webhooks or signatures where required.
- Avoid calling Wallet or Ledger.

---

## 5. Event Flow

### 5.1 Payment Events

```text
PaymentInitialized
PaymentSucceeded
PaymentFailed
PaymentWebhookProcessed
PaymentWalletCredited
```

Payment event flow:

```text
Payment initialized
  -> PaymentInitialized

Provider verification succeeds
  -> Wallet credited
  -> Ledger posted
  -> PaymentWalletCredited
  -> PaymentSucceeded

Provider verification fails
  -> PaymentFailed
```

### 5.2 Wallet Events

```text
WalletCreated
WalletCredited
WalletDebited
WalletReserved
WalletReleased
WalletRefunded
WalletFrozen
WalletUnfrozen
WalletAdjusted
```

Wallet events announce operational balance changes.

### 5.3 Ledger Events

```text
LedgerEntryPosted
LedgerBatchCompleted
LedgerReconciled
LedgerEntryReversed
```

Ledger events announce accounting postings and reconciliation outcomes.

### 5.4 Verification Events

```text
VerificationRequested
VerificationSubmitted
VerificationCompleted
VerificationFailed
VerificationRefunded
ProviderRequestSent
ProviderResponseReceived
```

Verification event flow:

```text
Verification requested
  -> Wallet reserved
  -> Provider request sent
  -> Provider response received

Provider success
  -> Reservation released
  -> Wallet debited
  -> Ledger posted
  -> VerificationCompleted

Provider failure
  -> Reservation released
  -> VerificationFailed
```

---

## 6. Data Flow

### 6.1 Wallet Funding Flow

```text
Customer
  -> Payment Service
  -> Payment Provider Manager
  -> Paystack Adapter
  -> Paystack
  -> Callback / Webhook
  -> Payment Service
  -> Server-side verification
  -> Wallet Credit Service
  -> Ledger Posting Service
  -> Payment Events
  -> Notifications later
```

Critical rules:

- Callback data alone must never credit wallet.
- Paystack must be verified server-side.
- Payment finalization must be idempotent.
- Wallet credit and ledger posting must be atomic.

### 6.2 Verification Flow

```text
Customer / Tenant
  -> Verification Service
  -> Pricing Resolution
  -> Wallet Reservation
  -> Verification Provider Manager
  -> SwiftVerify Adapter
  -> SwiftVerify
  -> Provider Response
  -> Reservation Release
  -> Wallet Debit
  -> Ledger Posting
  -> Verification Events
  -> Notifications later
```

Critical rules:

- Pricing must resolve before provider request.
- Price charged must be stored on request.
- Provider failure must release reserved funds.
- Provider success must debit wallet and post ledger.

### 6.3 Ledger Data Flow

```text
Business Module
  -> LedgerPostingInterface
  -> LedgerValidationService
  -> LedgerBatch
  -> LedgerEntries
  -> Ledger Events
```

Critical rules:

- Debits must equal credits.
- Posted entries are append-only.
- Corrections require reversal batches.
- Ledger is the historical financial source of truth.

---

## 7. Provider Abstractions

### 7.1 Payment Providers

Contract:

- `PaymentProviderContract`

First adapter:

- `PaystackAdapter`

Responsibilities:

- Initialize payment.
- Verify payment.
- Verify webhook signature.
- Normalize provider responses.

Future providers must implement the same contract.

### 7.2 Verification Providers

Contract:

- `VerificationProviderContract`

First adapter:

- `SwiftVerifyAdapter`

Responsibilities:

- Submit verification request.
- Normalize provider response.
- Support provider service code mapping.
- Avoid wallet and ledger logic.

Future providers must implement the same contract.

---

## 8. Wallet and Ledger Interaction

Wallet is operational. Ledger is accounting.

Wallet answers:

- What can the tenant spend now?
- What is reserved?
- What is frozen?
- What was credited or debited operationally?

Ledger answers:

- What is the official accounting history?
- What liabilities, revenue, expenses, and payables exist?
- Are financial postings balanced?
- Can finance reconcile wallet balances against accounting entries?

Allowed interaction:

```text
Wallet operation completed
  -> Wallet transaction created
  -> Ledger posting through interface
```

The Ledger must not mutate Wallet balances.

The Wallet must not bypass Ledger for financial history.

---

## 9. Chart of Accounts Requirement

Before production, IVP must include a chart-of-accounts setup phase.

Required launch accounts include:

- `cash_clearing`
- `tenant_wallet_liability`
- `platform_verification_revenue`

Future accounts should include:

- `provider_cost`
- `provider_payable`
- `refund_liability`
- `adjustment_account`
- `commission_payable`
- `promotion_expense`
- `subscription_revenue`
- `api_billing_revenue`

Payment and Verification finalization depend on these accounts existing.

---

## 10. Future Module Integration Rule

Every future paid service should follow this integration model:

```text
Service Request
  -> Service Domain
  -> Pricing
  -> Wallet reservation or debit
  -> Provider adapter
  -> Provider response
  -> Wallet capture/debit/refund/release
  -> Ledger posting
  -> Domain events
  -> Notifications later
```

Applies to:

- Airtime.
- Data.
- Electricity.
- Cable TV.
- WAEC.
- NECO.
- JAMB.
- Bulk verification.
- API billing.
- Subscriptions.
- Marketplace services.

---

## 11. Development Guardrails

Future phases must follow these guardrails:

- No business logic in controllers.
- No direct wallet balance changes outside Wallet services.
- No direct ledger entries outside Ledger services.
- No provider calls outside provider adapters.
- No notifications inside financial transaction services.
- No hardcoded credentials.
- No cross-tenant data access.
- No UI-first implementation before domain rules are stable.
- No module may reach into another module's database tables directly unless explicitly documented through an ADR.

---

## 12. Current Deferred Work

Not yet implemented:

- Chart-of-accounts seeding phase.
- Notification listeners.
- Reporting.
- Dashboard UI.
- Public API.
- Support.
- Affiliate.
- Promotions.
- Marketplace.
- Additional payment providers.
- Additional verification providers.

These should be added in controlled phases after CTO approval.
