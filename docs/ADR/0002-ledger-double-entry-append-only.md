# ADR 0002: Double-Entry Append-Only Ledger

## Status

Accepted for Phase 3.

## Context

IVP requires a financial accounting backbone that can support wallet funding, verification charges, provider costs, refunds, commissions, promotions, subscription billing, API billing, and future utility services. Wallet balances are useful for operations, but they are not sufficient as permanent accounting history.

## Decision

The ledger engine will use double-entry accounting. Every posting must be balanced, with total debits equal to total credits. Posted ledger entries are immutable and append-only. Corrections must be posted through reversal batches rather than editing existing entries.

## Advantages

- Produces auditable financial history.
- Prevents silent balance manipulation.
- Supports revenue, cost, liability, refund, and payable reporting.
- Gives reconciliation a stable source of truth.
- Allows future services to share the same accounting backbone.

## Disadvantages

- More complex than a simple transaction table.
- Requires a chart of accounts.
- Future modules must understand which accounts to post against.
- Reconciliation must distinguish operational wallet balances from accounting balances.

## Alternatives Considered

- Continue using wallet transactions as the only financial record.
- Implement a third-party accounting ledger.
- Delay ledger implementation until after Paystack integration.

## Why Not Alternatives

Wallet transactions alone cannot model platform revenue, provider costs, liabilities, commissions, or reversal workflows cleanly. A third-party ledger adds vendor risk and integration complexity. Delaying ledger implementation would force payment and verification modules to be rewritten later.

## Consequences

All future financial modules must post balanced journal entries into the ledger. Wallet remains an operational balance module, while ledger becomes the historical accounting source of truth.
