# Wallet Architecture

## Scope

Phase 2 implements only the Enterprise Wallet Domain. It does not implement Paystack, SwiftVerify, verification services, reporting, notifications, dashboard UI, API endpoints, affiliate, promotions, or ledger engine tables.

## Domain Structure

The wallet module lives under `app/Domain/Wallet` and is organized around:

- Models: relationship-only Eloquent models.
- Services: all financial operation logic.
- Interfaces: repository, service, balance, and reservation contracts.
- Value Objects: money, currency, balance, and transaction references.
- Enums: statuses and transaction types.
- Events: wallet domain events without listeners.
- Validators: reusable financial validation rules.

## Balance Rules

Wallets maintain five operational balances:

- Available Balance: spendable funds.
- Pending Balance: expected funds not yet available.
- Frozen Balance: funds blocked for compliance, fraud review, suspension, or admin action.
- Reserved Balance: funds held for in-progress operations.
- Refund Balance: funds returned from cancelled or reversed operations.

The wallet services update the wallet summary columns and keep the related `wallet_accounts` records synchronized.

## Transaction Lifecycle

Wallet transactions are created for:

- Credit.
- Debit.
- Reservation.
- Release.
- Refund.
- Adjustment.

Each transaction has a unique reference and may have an idempotency key. Successful balance mutations are recorded in `wallet_transactions`. Ledger tables are intentionally excluded from Phase 2 and will be introduced later by the Ledger Engine phase.

## Reservation Flow

1. Validate wallet is active.
2. Validate currency and positive amount.
3. Lock the wallet row.
4. Confirm sufficient available balance.
5. Move funds from available balance to reserved balance.
6. Create reservation transaction.
7. Create active wallet reservation.
8. Dispatch `WalletReserved`.

Release reverses the hold by moving funds from reserved balance back to available balance. Full releases close the reservation; partial releases keep it active.

## Error Handling

Wallet operations throw domain-specific exceptions:

- `InsufficientFundsException`.
- `DuplicateTransactionReferenceException`.
- `InvalidWalletOperationException`.
- `WalletException`.

These exceptions are intentionally domain-specific so future API, UI, and job layers can translate them into appropriate responses without embedding wallet rules outside the domain.

## Concurrency Strategy

The wallet prevents double spending using:

- Database transactions around every mutation.
- Row-level locks through `lockForUpdate()`.
- Unique transaction references.
- Optional idempotency keys.
- Validation immediately before mutation while the row is locked.

This means concurrent debits, reservations, refunds, and adjustments serialize against the same wallet row.

## Deferred Work

The following are explicitly deferred:

- Payment gateway credit source integration.
- Verification charge integration.
- Wallet dashboards.
- Wallet API endpoints.
- Wallet notifications and listeners.

## Ledger Integration

Phase 3 adds a wallet ledger bridge through `WalletLedgerPostingInterface`. Wallet services still own operational balance mutation, while the ledger module owns accounting history. Future workflows can post wallet transactions to ledger accounts without coupling wallet services to ledger persistence.
