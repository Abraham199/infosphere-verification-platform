# Payment Security

## Server-Side Verification

No wallet credit is allowed from callback data alone. The Payment Service verifies the transaction with Paystack before marking the payment successful.

## Webhook Signature Verification

Paystack webhooks are validated with HMAC SHA-512 using the Paystack secret key. Invalid signatures are recorded and rejected.

## Replay Attack Prevention

Webhook replay protection uses:

- Provider event reference.
- Signature hash.
- Unique webhook event records.
- Idempotent wallet credit key.

## Duplicate Callback Protection

Payment finalization locks the payment transaction row before status change. If a payment is already successful, repeated verification returns the existing successful payment without creating another wallet credit.

## Transaction Reference Validation

Payment references must match a strict uppercase alphanumeric, underscore, and dash format. References are unique in `payment_transactions`.

## Provider Logs

Provider requests and responses are recorded in `payment_provider_logs` for audit and troubleshooting. Sensitive fields should be masked before production logging rules are finalized.

## Secrets

Paystack credentials must be stored in environment variables:

- `PAYSTACK_BASE_URL`
- `PAYSTACK_PUBLIC_KEY`
- `PAYSTACK_SECRET_KEY`

No credentials should be committed to source control.
