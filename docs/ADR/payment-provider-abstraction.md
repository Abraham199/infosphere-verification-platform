# ADR: Payment Provider Abstraction

## Status

Accepted for Phase 4.

## Context

IVP launches with Paystack, but the platform is intended to support additional payment providers over time. Payment logic must not be embedded directly inside wallet services, controllers, or provider-specific classes.

## Decision

Create a provider-agnostic Payment Domain. Payment Service owns payment initialization, verification, webhooks, idempotency, state transitions, wallet crediting, and ledger posting. Provider-specific calls are hidden behind `PaymentProviderContract`.

Paystack is implemented as the first adapter.

## Advantages

- Prevents Paystack lock-in.
- Keeps Wallet independent from payment providers.
- Allows future providers to be added behind the same contract.
- Centralizes payment state transitions and security checks.
- Makes webhook replay and idempotency logic consistent.

## Disadvantages

- Adds abstraction before a second provider exists.
- Provider-specific features must be normalized carefully.
- Adapter test coverage is required to avoid hidden integration differences.

## Alternatives

- Direct Paystack calls from controllers.
- Direct Paystack calls from Wallet services.
- Separate payment implementation per provider.

## Rationale

Direct provider calls would violate the approved architecture and make future providers expensive to add. A dedicated Payment Domain protects wallet and ledger boundaries while still allowing Paystack to launch first.
