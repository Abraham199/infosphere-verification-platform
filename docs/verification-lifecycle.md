# Verification Lifecycle

## States

- Created.
- Validating.
- Submitted.
- Processing.
- Completed.
- Failed.
- Cancelled.
- Refunded.

## Valid Transitions

- created -> validating
- created -> cancelled
- validating -> submitted
- validating -> failed
- validating -> cancelled
- submitted -> processing
- submitted -> completed
- submitted -> failed
- processing -> completed
- processing -> failed
- completed -> refunded
- failed -> refunded

## Success Flow

1. Resolve service and pricing.
2. Reserve wallet funds.
3. Submit provider request.
4. Release reservation.
5. Debit wallet.
6. Post ledger entries.
7. Store verification result.
8. Emit completion event.

## Failure Flow

1. Resolve service and pricing.
2. Reserve wallet funds.
3. Submit provider request.
4. If provider fails, release reservation.
5. Mark request failed.
6. Emit failure event.

Notifications are deferred to a future phase.
