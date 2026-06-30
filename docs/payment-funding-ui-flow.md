# Payment Funding UI Flow

## Purpose

This document describes how wallet funding is initiated from the tenant UI without bypassing the existing Payment Platform.

## Flow

Tenant User
-> Wallet Funding Form
-> Funding Request Validation
-> Tenant Wallet Lookup
-> `PaymentServiceInterface::initialize`
-> Payment Provider Adapter
-> Paystack Initialization
-> Payment Status Page

## Controller Boundary

The tenant wallet controller creates a `PaymentInitializationData` DTO and passes it to the Payment Service. It does not know Paystack details and does not call any provider directly.

## Validation

The funding request validates:

- Amount is required, numeric, at least NGN 100, and capped at NGN 5,000,000
- Currency is NGN
- Email is a valid RFC email address

## Status Handling

After initialization, the user is redirected to:

`tenant.wallet.funding.show`

The status page reads the tenant-scoped `payment_transactions` record and displays:

- Payment reference
- Amount
- Status
- Provider authorization link when available
- Failure reason when available

## Failure Behavior

If no tenant wallet exists, the form returns a validation error.

If payment initialization throws an exception, the controller logs a warning and returns a generic user-facing error without exposing provider internals.

## Security

- Existing auth middleware protects all routes.
- Existing verified middleware protects all routes.
- Existing tenant middleware resolves and scopes tenant access.
- Existing permission middleware requires `dashboard.view`.
- Payment status lookup requires both tenant ID and payment reference.
