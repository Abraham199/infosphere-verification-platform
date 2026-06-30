# Support Assignment

The Assignment Engine supports:

- manual assignment
- team assignment
- auto-assignment framework
- escalation readiness

## Rules

Assignments require either a user or a team. Auto-assignment is represented as an assignment type, but no AI or routing algorithm is implemented in Phase 9.

Escalation assignments emit `TicketEscalated`. Normal user/team assignments emit `TicketAssigned`.
