# Service Catalog

Verification services are represented by `verification_services`.

Provider-specific mappings live in `provider_services`.

This separates what IVP sells from how a provider fulfills it:

- IVP service: `nin_lookup`
- Provider mapping: `swift_nin`

Benefits:

- Multiple providers can support the same IVP service.
- Provider priority can be changed without changing customer-facing service codes.
- Services can be disabled independently of provider mappings.
- Future providers can be added without changing verification business logic.
