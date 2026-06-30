# Provider Mapping

Products do not know providers directly. Provider fulfillment is resolved through `product_provider_mappings`.

Example:

```text
NIN Verification
  -> SwiftVerify mapping
  -> Future Provider mapping
```

Provider mappings include:

- Product.
- Provider key.
- Provider product code.
- Status.
- Priority.
- Configuration.

The resolver selects the active provider mapping with the lowest priority value. Future provider managers can consume this mapping without changing product definitions.
