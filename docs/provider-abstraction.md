# Verification Provider Abstraction

The verification platform uses provider contracts so SwiftVerify is only the first adapter, not a core dependency.

Core contracts:

- `VerificationProviderContract`
- `VerificationProviderManagerInterface`
- `VerificationProviderInterface`
- `VerificationResponseInterface`

Provider adapters must:

- Declare support for provider service codes.
- Submit provider requests.
- Normalize provider responses.
- Avoid hardcoded credentials.
- Apply timeout handling.
- Return a stable `VerificationProviderResponse`.

Future providers can be added by implementing the same contract and registering them in the provider manager.
