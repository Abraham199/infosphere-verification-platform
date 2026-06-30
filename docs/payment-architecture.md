# Payment Architecture

## Scope

Phase 4 implements the Enterprise Payment Platform only. It does not implement SwiftVerify, verification services, airtime/data, notifications, reporting, dashboard UI, support, affiliate, promotions, or public API.

## Flow

```text
Customer
  -> Payment Service
  -> Payment Provider Adapter
  -> Paystack
  -> Webhook / Callback
  -> Payment Service
  -> Wallet Service
  -> Ledger
  -> Events
  -> Notifications later
```

## Domain Structure

The payment module lives under `app/Domain/Payment` and contains:

- DTOs for initialization, provider responses, and webhook input.
- Enums for provider, status, method, attempt status, and webhook status.
- Models for payment transactions, attempts, webhooks, and provider logs.
- Interfaces for payment service, provider manager, and repository.
- Services for initialization, verification, webhook handling, provider selection, and state transitions.
- Validators for reference, replay, amount, and idempotency rules.
- Events for downstream listeners in later phases.

## Provider Abstraction

`PaymentProviderContract` defines the provider boundary:

- Initialize payment.
- Verify payment.
- Verify webhook signature.

Paystack is implemented in `App\Infrastructure\Paystack\PaystackAdapter`. Future providers must implement the same contract and be selected by `PaymentProviderManager`.

## Payment Boundaries

- Payment owns provider calls, callbacks, webhooks, payment status, attempts, and provider logs.
- Wallet only credits funds after Payment verifies success server-side.
- Ledger receives accounting entries after wallet credit is confirmed.
- Notifications are intentionally deferred and will listen to payment events later.
- Wallet never calls Paystack directly.

## Ledger Posting

Successful payments post to ledger through the existing wallet-ledger bridge:

- Debit `cash_clearing`.
- Credit `tenant_wallet_liability`.

If required ledger accounts are missing, payment finalization fails atomically and the wallet credit is rolled back with the surrounding transaction.

## Deferred Work

- Payment controllers and UI.
- Public API payment endpoints.
- Notification listeners.
- Provider settlement reconciliation.
- Additional payment providers.
