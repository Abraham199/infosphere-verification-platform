# API Authentication

Phase 10 prepares several authentication modes:

- API keys
- personal access tokens
- service-to-service tokens
- OAuth readiness

## API Keys

API keys are issued once as plain text and stored only as SHA-256 hashes. They support:

- tenant ownership
- API client ownership
- scopes
- production or sandbox environment
- expiration
- revocation

## Personal Access Tokens

Tokens are also stored as hashes and may be attached to users, API clients, or tenants.

## OAuth Readiness

OAuth providers are not implemented in Phase 10. The token table includes token type support so OAuth can be added later without replacing the credential model.
