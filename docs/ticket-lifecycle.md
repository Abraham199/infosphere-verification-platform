# Ticket Lifecycle

## Statuses

- `open`
- `assigned`
- `in_progress`
- `waiting_for_customer`
- `resolved`
- `closed`
- `reopened`
- `cancelled`

## Valid Transitions

```mermaid
stateDiagram-v2
    [*] --> open
    open --> assigned
    open --> in_progress
    open --> cancelled
    assigned --> in_progress
    assigned --> waiting_for_customer
    assigned --> resolved
    assigned --> cancelled
    in_progress --> waiting_for_customer
    in_progress --> resolved
    in_progress --> cancelled
    waiting_for_customer --> in_progress
    waiting_for_customer --> resolved
    waiting_for_customer --> cancelled
    resolved --> closed
    resolved --> reopened
    closed --> reopened
    reopened --> assigned
    reopened --> in_progress
    reopened --> cancelled
```

Invalid transitions raise `InvalidTicketStatusTransitionException`.
