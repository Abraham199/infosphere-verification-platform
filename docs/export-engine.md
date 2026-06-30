# Export Engine

Phase 8 prepares export requests only. It does not build dashboard UI.

## Supported Formats

- CSV
- Excel
- PDF

`ExportEngine` creates a `reporting_exports` row with `pending` status and a unique export reference. Actual file rendering can be handled by queued workers in a later hardening phase.

## Duplicate Protection

Callers may provide a dedupe key. Duplicate export references are rejected by validation and the database unique constraint.
