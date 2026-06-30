# Webhook Platform

The webhook platform provides endpoint registration, event persistence, delivery records, HMAC signing, signature verification, retry readiness, and idempotency references.

## Tables

- `webhook_endpoints`
- `webhook_events`
- `webhook_deliveries`

## Security

Webhook payloads are signed with HMAC SHA-256. Consumers should verify signatures before processing payloads.

## Retry Framework

`WebhookRetryService` supports exponential backoff and failure tracking. Actual outbound HTTP delivery workers are reserved for later hardening.
