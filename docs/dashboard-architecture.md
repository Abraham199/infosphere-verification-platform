# Dashboard Architecture

## Goal

Dashboards expose the stable IVP backend through read-only UI adapters. They summarize data without introducing new domain behavior.

## Flow

Controller
-> View Model
-> Existing read tables / reporting tables / safe aggregate reads
-> Blade view
-> Design system components

## View Data

Sprint 1 uses `App\Http\ViewModels\ExperienceDashboardData` to prepare dashboard data for:

- Platform overview
- Tenant dashboard
- Wallet views
- Verification views
- Product management views

This keeps database reads out of Blade and keeps controllers thin.

## Reporting Cards

Reusable cards are implemented for:

- Revenue
- Wallet
- Verification
- Payment
- Product
- Tenant
- Notification
- Support

Full analytics pages are intentionally deferred.

## Boundaries

The dashboard layer does not:

- Credit or debit wallets
- Post ledger entries
- Call Paystack
- Call SwiftVerify
- Create verification requests
- Change product capability rules
