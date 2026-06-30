# Product Lifecycle

## Statuses

- Draft.
- Active.
- Disabled.
- Maintenance.
- Deprecated.
- Archived.

## Valid Transitions

- draft -> active
- draft -> disabled
- draft -> archived
- active -> disabled
- active -> maintenance
- active -> deprecated
- active -> archived
- disabled -> active
- disabled -> archived
- maintenance -> active
- maintenance -> disabled
- maintenance -> archived
- deprecated -> disabled
- deprecated -> archived

Archived products are terminal.

Product state changes emit product domain events. Notification listeners are intentionally deferred.
