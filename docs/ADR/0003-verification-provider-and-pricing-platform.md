# ADR 0003: Verification Provider and Pricing Platform

## Status

Accepted for Phase 5.

## Context

IVP launches with SwiftVerify, but the platform must support future verification providers. The platform also requires tenant-specific pricing, global pricing, and default service pricing before provider requests are submitted.

## Decision

Implement a provider-agnostic Verification Domain with a service catalog, provider service mappings, tenant pricing rules, provider manager, and SwiftVerify adapter.

Tenant-specific pricing is stored in `verification_pricing_rules`.

## Advantages

- SwiftVerify does not leak into core verification logic.
- Future providers can be added through adapters.
- Customer-facing service codes are independent of provider service codes.
- Price charged is stored on each request for historical accuracy.
- Wallet and ledger integration remains interface-driven.

## Disadvantages

- More tables are required than a direct provider integration.
- Pricing and provider mapping must be maintained operationally.
- Chart-of-account setup must include verification revenue accounts before production use.

## Alternatives

- Direct SwiftVerify integration.
- Store tenant prices in generic tenant settings.
- Hardcode provider service codes in verification services.

## Rationale

Direct provider integration would create vendor lock-in. Generic tenant settings would make pricing harder to validate and audit. A dedicated pricing rule table keeps pricing explicit, queryable, and compatible with future reporting.
