# Notification Preferences

Preferences live in `notification_preferences` and may be defined globally, per tenant, or per user.

## Preference Precedence

1. User preference.
2. Tenant preference.
3. Global preference.
4. Enabled by default.

## Mandatory Categories

Financial and security notifications are mandatory. They cannot be disabled even if a tenant or user preference attempts to opt out.

Mandatory examples:

- wallet funded
- wallet debited
- payment succeeded
- payment failed
- refund issued
- password or access security alerts

Optional examples:

- marketing campaigns
- product education
- non-critical service updates
