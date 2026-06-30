# ADR 0001: Wallet Row Locking and Idempotency

## Status

Accepted for Phase 2.

## Context

The IVP wallet is the financial foundation for future payments, verification charges, refunds, reservations, and adjustments. The platform must prevent double spending when multiple requests attempt to mutate the same wallet concurrently.

## Decision

Wallet mutation services will wrap every balance change in a database transaction and lock the affected wallet row with row-level locking before validation and mutation. Each wallet transaction will require a unique reference and may include an idempotency key.

## Consequences

### Advantages

- Prevents concurrent requests from spending the same available balance.
- Keeps validation and mutation atomic.
- Makes retry-safe workflows possible through idempotency keys.
- Works with MySQL on the approved shared-database architecture.

### Disadvantages

- High-volume tenants can create contention on a single wallet row.
- Long-running operations must not happen while the wallet row is locked.
- Provider calls must occur outside wallet locks in later phases.

## Alternatives Considered

- Optimistic locking with version columns.
- External wallet service.
- Immediate double-entry ledger-first implementation.

## Why Not Alternatives Now

Optimistic locking requires careful retry behavior and is easier to misuse in financial flows. An external wallet service adds cost and dependency risk. A full ledger engine is approved for a later phase, so Phase 2 keeps wallet transactions ready for ledger integration without creating ledger tables early.

## Future Review

If transaction volume grows enough to cause wallet row contention, IVP can introduce dedicated wallet queues, tenant wallet sharding, or a ledger-first posting engine with asynchronous balance projections.
