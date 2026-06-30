# ADR 0004: Central Product Engine

## Status

Accepted for Phase 6.

## Context

IVP is expanding beyond identity verification into a broader digital services platform. Hardcoded service definitions would create repeated work across verification, airtime, data, electricity, education services, API products, subscriptions, and marketplace features.

## Decision

Create a central Product Engine with configurable categories, products, provider mappings, tenant availability, product features, and capabilities.

## Advantages

- Reduces hardcoded services.
- Gives every business module a shared catalog.
- Supports tenant-specific availability and price overrides.
- Allows future providers to be mapped without changing product identity.
- Provides a foundation for future marketplace and API products.

## Disadvantages

- Adds an additional abstraction layer.
- Product configuration must be operationally maintained.
- Existing verification service catalog may later need a controlled migration into the Product Engine.

## Alternatives

- Keep separate catalogs per module.
- Hardcode services in each module.
- Store product settings in generic tenant settings.

## Rationale

A central Product Engine makes IVP more scalable and commercial. Separate catalogs would duplicate pricing, provider mapping, capability, and tenant availability logic across modules.
