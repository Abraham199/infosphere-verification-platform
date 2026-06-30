# Verification User Flow

## Operational Flow

Tenant User
-> Verification Form
-> Request Validation
-> Verification Pricing Engine
-> Wallet Balance Check
-> `VerificationServiceInterface`
-> Wallet Reservation
-> Provider Manager
-> SwiftVerify Adapter
-> Provider Response
-> Wallet Debit on Success
-> Reservation Release
-> Ledger Posting
-> Verification Result Screen

## Success Path

1. User opens the verification request page.
2. User selects a verification service.
3. UI displays tenant-resolved pricing.
4. User enters subject details.
5. Controller validates request input.
6. Controller checks wallet presence and available balance for a friendly pre-submit message.
7. Controller submits a `VerificationRequestData` DTO to the Verification Platform.
8. Verification Platform handles reservation, provider submission, debit, ledger posting, events, and result creation.
9. User is redirected to the verification status page.

## Failure Handling

- Invalid input returns validation errors.
- Missing tenant wallet returns a friendly wallet error.
- Insufficient balance blocks submission before provider interaction.
- Domain exceptions are displayed safely.
- Unexpected exceptions are logged and shown as a generic retry/support message.

## Tenant Isolation

Verification status and result lookups require both:

- Current tenant context
- Verification reference

This prevents one tenant from viewing another tenant's verification record.
