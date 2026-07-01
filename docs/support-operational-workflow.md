# Support Operational Workflow

Sprint 2 Milestone 4 exposes the existing Enterprise Support Desk Engine through authenticated Blade screens.

## Tenant Workflow

Tenant users require:

- `support.tickets.view`
- `support.tickets.create`
- `support.tickets.comment`

Tenant support pages are available under `/t/{tenant}/support`.

Supported tenant operations:

- create a ticket for the resolved tenant
- view tenant-scoped ticket history
- view a ticket by reference
- add public replies
- read published knowledge base articles

Tenant views never render internal notes and every ticket lookup is constrained by the resolved tenant context.

## Platform Workflow

Platform support pages are available under `/platform/support` and remain restricted to the `Super Admin` role.

Supported platform operations:

- view the cross-tenant support queue
- inspect ticket details, SLA state, notes, and assignment history
- assign a ticket to a team or user through the Assignment Engine
- transition ticket status through the Ticket Service lifecycle rules
- add staff-only internal notes

## Architecture Rules

- Controllers validate requests and delegate to `SupportDeskWorkflowService`.
- `SupportDeskWorkflowService` uses existing Support Domain services and models.
- No support tables were added for this milestone.
- Blade files remain presentation-only and do not contain domain rules.
- Support still emits domain events rather than sending notifications directly.
