# ADR: Notification Platform Design

## Status

Accepted for Phase 7.

## Context

IVP domains emit important business events for wallet, ledger, payment, verification, product, and security actions. Sending notifications directly from those domains would tightly couple business logic to communication providers.

## Decision

Create a provider-agnostic Notification Platform with:

- domain-owned notification service
- template resolver with tenant override and global fallback
- tenant and user preference enforcement
- channel manager
- email channel adapter
- delivery and retry logs
- contracts for future SMS, WhatsApp, In-App, and Push adapters

## Consequences

Domains remain event-driven and loosely coupled. New channels can be added behind adapter contracts without changing wallet, payment, ledger, product, or verification flows.

The first delivery channel is email. Future channel adapters require explicit CTO approval.
