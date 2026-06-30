# Pricing Engine

Pricing is resolved before any provider request is sent.

Priority:

1. Tenant-specific pricing from `verification_pricing_rules`.
2. Global service pricing from `verification_services.global_price`.
3. Default service pricing from `verification_services.default_price`.

The resolved price is stored as `price_charged` on each verification request. This preserves historical accuracy after price changes.

Zero pricing is rejected unless a future promotion module explicitly authorizes it.
