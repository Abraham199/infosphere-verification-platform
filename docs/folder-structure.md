# Folder Structure

- `app/Domain`: domain-specific models, services, policies, scopes, and events.
- `app/Services`: application-level services.
- `app/Contracts`: integration and module contracts.
- `app/Infrastructure`: provider adapters.
- `app/Repositories`: persistence abstractions for complex data access.
- `app/Http/Controllers/Platform`: platform administration controllers.
- `app/Http/Controllers/Tenant`: tenant workspace controllers.
- `app/Http/Controllers/Api`: future API controllers.
- `app/Http/Middleware`: request, security, and tenant middleware.
- `app/Policies`: authorization policies.
- `app/Domain/Wallet`: enterprise wallet domain models, services, events, interfaces, validators, and value objects.
- `app/Domain/Ledger`: double-entry ledger engine models, services, events, interfaces, validators, repositories, and value objects.
- `app/Domain/Payment`: provider-agnostic payment domain models, services, events, interfaces, DTOs, validators, repositories, and provider orchestration.
- `app/Domain/Verification`: provider-agnostic verification domain models, services, events, interfaces, DTOs, validators, repositories, service catalog, pricing, and provider orchestration.
- `app/Domain/Product`: central Product Engine models, services, events, interfaces, DTOs, validators, repositories, provider mapping, tenant availability, and capabilities.
- `resources/views/layouts`: guest, platform, and tenant layouts.
- `routes`: web, API, and console route definitions.
