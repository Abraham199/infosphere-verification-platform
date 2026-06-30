# ADR: Support Desk Platform Architecture

## Status

Accepted for Phase 9.

## Context

IVP needs a centralized enterprise support layer for customer issues across payments, wallets, verification, products, and account operations. Support workflows should not live inside operational domains because support concerns have their own lifecycle, SLA, assignments, notes, and knowledge base.

## Decision

Create a standalone Support Domain with ticket management, configurable categories and priorities, assignment engine, SLA engine, internal notes, attachment metadata, and knowledge base foundation.

Support emits events for notifications and analytics to consume later. It does not send notifications directly and does not query or mutate operational business modules.

## Consequences

Support becomes the system of record for customer support activities while remaining loosely coupled to the rest of IVP. Later dashboard, notification, and analytics integrations can subscribe to Support events without changing ticket workflows.
