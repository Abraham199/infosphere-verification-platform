# Navigation

## Platform Navigation

Base route prefix: `/platform`

- Overview: `platform.dashboard`
- Wallets: `platform.wallet.index`
- Verifications: `platform.verification.index`
- Products: `platform.products.index`

Access is restricted by existing authentication, email verification, and `Super Admin` role middleware.

## Tenant Navigation

Base route prefix: `/t/{tenant:slug}`

- Dashboard: `tenant.dashboard`
- Wallet: `tenant.wallet.index`
- Verification: `tenant.verification.index`
- Products: `tenant.products.index`
- Customer: `tenant.customer.dashboard`

Access uses existing authentication, email verification, and tenant resolution middleware.

## Authentication Navigation

- Login
- Forgot password
- Reset password
- Email verification
- Logout

## Mobile Behavior

The sidebar collapses into a compact responsive grid on smaller screens. Navigation targets remain visible and keyboard accessible.
