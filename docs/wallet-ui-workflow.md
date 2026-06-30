# Wallet UI Workflow

## Scope

Sprint 2 Milestone 1 turns the Sprint 1 wallet UI foundation into an operational tenant wallet workflow.

## Routes

Tenant wallet routes live under the existing tenant route group:

- `GET /t/{tenant:slug}/wallet`
- `GET /t/{tenant:slug}/wallet/transactions`
- `GET /t/{tenant:slug}/wallet/funding`
- `POST /t/{tenant:slug}/wallet/funding`
- `GET /t/{tenant:slug}/wallet/funding/{reference}`
- `GET /t/{tenant:slug}/wallet/reservations`

All routes use existing authentication, email verification, tenant resolution, and `dashboard.view` permission middleware.

## Pages

### Wallet Overview

Shows tenant wallet balances:

- Available balance
- Reserved balance
- Frozen balance
- Wallet status

If no wallet exists, the UI renders an empty state and does not attempt to create a wallet.

### Transactions

Displays wallet transaction history with:

- Reference
- Type
- Amount
- Balance after transaction
- Status
- Created timestamp

### Funding

Displays:

- Wallet funding form
- Funding history
- Validation errors
- Empty wallet state

Funding is initialized through the Payment Platform boundary.

### Funding Status

Displays a payment transaction status after initialization:

- Pending
- Failed
- Success
- Initialized / other states

If an authorization URL exists, the page presents a provider continuation button.

### Reservations

Displays reserved, captured, and released amounts for wallet reservations.

## Boundary Rules

- Blade views do not perform business operations.
- Wallet UI does not call Paystack.
- Wallet funding uses `PaymentServiceInterface`.
- Tenant isolation is enforced by tenant middleware and tenant-scoped payment lookup.
- No new database tables were added.
