# ADR: Reporting and Analytics Architecture

## Status

Accepted for Phase 8.

## Context

IVP now contains operational domains for wallet, ledger, payment, verification, product, and notification workflows. Querying those tables directly from dashboards would couple reporting to business modules and create performance pressure as tenants and transaction volume grow.

## Decision

Create a separate Reporting Domain that consumes domain events into a reporting store. KPI calculations, report snapshots, export requests, and scheduled reports operate on reporting tables.

## Consequences

Dashboards and exports can scale independently from transactional workflows. Operational modules remain responsible for business correctness, while Reporting remains responsible for read models and analytics.

Backfills and reconciliation may still require controlled operational reads, but normal reporting paths should use the reporting store.
