# Payment Lifecycle

## Initialization

1. Customer requests wallet funding.
2. Payment Service validates amount and reference.
3. Payment Service creates `payment_transactions` record.
4. Payment Service calls provider adapter.
5. Provider returns authorization URL and provider reference.
6. Payment status becomes pending if provider initialization succeeds.

## Verification

1. Paystack callback or webhook provides a transaction reference.
2. Payment Service performs server-side verification with Paystack.
3. Failed verification marks payment failed.
4. Successful verification transitions payment to success.
5. Wallet Service credits the wallet using an idempotency key.
6. Ledger receives the accounting posting.
7. Payment events are emitted.

## Webhook Processing

1. Payment Service receives raw payload and signature.
2. Provider adapter verifies signature.
3. Service checks replay protection using event reference and signature hash.
4. Service stores webhook record.
5. Service verifies payment server-side.
6. Service marks webhook processed.

## State Machine

Allowed transitions:

- initialized -> pending
- initialized -> success
- initialized -> failed
- initialized -> cancelled
- initialized -> abandoned
- pending -> success
- pending -> failed
- pending -> cancelled
- pending -> abandoned
- success -> reversed

Terminal states:

- failed
- cancelled
- abandoned
- reversed

## Idempotency

Initialization supports idempotency keys. Wallet credit uses `payment-credit-{payment_id}` as an idempotency key so repeated callbacks cannot credit the wallet more than once.
