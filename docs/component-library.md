# Component Library

## Components

Components live under `resources/views/components`.

## UI Components

- `x-ui.button`
- `x-ui.link-button`
- `x-ui.card`
- `x-ui.table`
- `x-ui.input`
- `x-ui.select`
- `x-ui.checkbox`
- `x-ui.alert`
- `x-ui.toast`
- `x-ui.modal`
- `x-ui.drawer`
- `x-ui.tabs`
- `x-ui.breadcrumbs`
- `x-ui.loading`
- `x-ui.empty-state`
- `x-ui.error-state`
- `x-ui.status-badge`
- `x-ui.stat-card`
- `x-ui.pagination`

## Dashboard Components

- `x-dashboard.metric-grid`

## Usage Rule

New screens should compose these components first. A new component should be introduced only when a repeated UI pattern appears across pages or when it improves accessibility and consistency.

## Accessibility

- Form controls use labels.
- Status badges render text, not color alone.
- Modal and drawer components include accessible labels.
- Focus states are defined globally.
- Empty states use text and icon cues.
