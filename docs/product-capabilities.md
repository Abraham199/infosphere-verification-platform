# Product Capabilities

Product capabilities describe operational behavior without hardcoding module logic.

Supported capabilities:

- `requires_wallet`
- `requires_reservation`
- `supports_refund`
- `supports_batch_processing`
- `supports_async_processing`
- `requires_provider`
- `requires_payment_first`

Capability rules are validated by `ProductCapabilityService`.

Important rule:

- Products requiring payment first must also require wallet support.

Future service modules should read capabilities before deciding whether to reserve funds, call providers, support refunds, or process work asynchronously.
