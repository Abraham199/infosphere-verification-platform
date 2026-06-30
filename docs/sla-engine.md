# SLA Engine

The SLA engine is configurable by tenant and priority.

## Supported Targets

- first response minutes
- resolution minutes
- escalation minutes
- business-hours metadata
- breach tracking

## Behavior

When a ticket is created, `SlaEngine` selects the best active policy for the tenant and priority. It then sets:

- `first_response_due_at`
- `resolution_due_at`
- `escalation_due_at`

If a due time passes before response or resolution, the ticket is marked breached and `SLAExceeded` is emitted.
