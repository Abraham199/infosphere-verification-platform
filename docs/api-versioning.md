# API Versioning

API versions are stored in `api_versions` and resolved through `ApiVersionResolver`.

## Rules

- URLs may include versions such as `/api/v1/`.
- Business modules must not depend on URL structure.
- Deprecated versions may remain resolvable.
- Sunset versions are rejected.
- A default version may be configured for clients that do not provide one.

## Future Strategy

OpenAPI contracts should be generated per major version. SDK major versions should follow API major versions.
