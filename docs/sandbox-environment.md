# Sandbox Environment

Sandbox support is isolated from production credentials and traffic.

## Supported Foundation

- sandbox developer applications
- sandbox API clients
- sandbox API keys
- sandbox webhook endpoints
- sandbox event naming
- sandbox metadata flagging

## Rules

- Sandbox keys use the `ivp_sandbox_` prefix.
- Sandbox resources are marked with `environment = sandbox`.
- Production traffic must not depend on sandbox resources.
