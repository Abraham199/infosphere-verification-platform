# ADR: Enterprise Developer Platform Architecture

## Status

Accepted for Phase 10.

## Context

IVP now has enterprise operational domains, reporting, notifications, and support. External clients and future mobile apps need a secure integration layer that does not couple directly to business modules or database structures.

## Decision

Create a standalone Developer Platform domain with API clients, API keys, tokens, versioning, rate limits, usage tracking, webhooks, sandbox support, SDK foundation, and API documentation foundation.

The Developer Platform routes requests through a gateway layer and interacts with business capabilities only through approved services and contracts.

## Consequences

Public and internal clients can be integrated through a controlled, versioned, auditable boundary. API usage can feed Reporting without dashboards querying operational traffic. OAuth, SDK generation, and richer API endpoints can be added later without redesigning the integration layer.

After Phase 10, development stops for the Enterprise Architecture Review.
