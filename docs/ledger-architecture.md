# Ledger Architecture

## Scope

Phase 3 implements the Enterprise Ledger Engine only. It does not implement Paystack, SwiftVerify, verification services, notification delivery, reporting dashboards, support, affiliate, promotions, public API, or dashboard UI.

## Purpose

The ledger is the single source of truth for financial history in IVP. Wallet balances remain operational projections, while the ledger records the accounting history that future payments, verification charges, refunds, subscriptions, API billing, utility services, commissions, and adjustments must post into.

## Double-Entry Design

Every journal entry is posted as a ledger batch containing at least two ledger entries. Total debits must equal total credits before posting is allowed.

Supported account types:

- Asset.
- Liability.
- Revenue.
- Expense.
- Equity.

Typical accounts include:

- Tenant Wallet Liability.
- Platform Revenue.
- Provider Cost.
- Provider Payable.
- Refund Liability.
- Adjustment Account.
- Commission Payable.
- Promotion Expense.

## Tables

- `ledger_accounts`: chart-of-account records.
- `ledger_batches`: balanced journal entry headers.
- `ledger_entries`: individual debit and credit lines.
- `reconciliation_runs`: reconciliation execution records.
- `reconciliation_results`: reconciliation outcomes and differences.

## Immutable Ledger

Posted ledger entries are append-only. Existing posted entries must not be edited or deleted by application workflows. Corrections are created through reversing batches that post equal and opposite entries.

Original entries remain posted. Reversal entries reference original entries through `reversal_of_entry_id`, and reversal batches reference the original batch through `reversal_of_batch_id`.

## Reversal Workflow

1. Load posted ledger batch and entries.
2. Validate the batch has not already been reversed.
3. Create a new journal entry with debit and credit directions swapped.
4. Post the reversal batch.
5. Link reversal entries to original entries.
6. Keep original entries unchanged.

## Reconciliation Strategy

Phase 3 includes framework-level reconciliation for wallet balances against tenant wallet liability accounts. It is designed to later compare:

- Wallet balances.
- Ledger account balances.
- Future Paystack records.
- Future SwiftVerify charges.
- Future subscription and API billing records.

Reconciliation outputs one run and one or more results with statuses:

- Pending.
- Matched.
- Difference found.
- Failed.

## Wallet Integration

Wallet does not depend on ledger models or repositories. Wallet can post accounting entries through `WalletLedgerPostingInterface`, implemented by `WalletLedgerPostingService`, which depends on `LedgerPostingInterface`.

This preserves module boundaries:

- Wallet owns operational balances and wallet transactions.
- Ledger owns accounting batches and entries.
- Future workflows decide when wallet transactions should be posted to ledger accounts.

## Financial Data Flow

Example wallet credit:

1. Wallet service credits operational balance.
2. Future workflow chooses the source account and tenant wallet liability account.
3. Wallet ledger bridge creates a balanced journal entry.
4. Ledger posting service validates and posts the batch.
5. Ledger becomes the financial history source of truth.

Example wallet debit:

1. Wallet service debits operational balance.
2. Future workflow chooses the tenant wallet liability account and revenue/expense/clearing account.
3. Ledger receives equal debit and credit lines.
4. Posting fails if the batch is unbalanced or accounts are invalid.

## Deferred Work

- Payment provider reconciliation.
- Verification charge posting.
- Dashboard and reporting UI.
- Ledger export reports.
- Automated listeners.
- Chart-of-account seeders for production tenants.
