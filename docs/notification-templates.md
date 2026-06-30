# Notification Templates

Templates live in `notification_templates` and support:

- `event_type`
- `channel`
- `subject`
- `title`
- `body`
- `variables`
- `tenant_id` for tenant override
- global defaults through `tenant_id = null`
- `status`
- `version`

## Resolution Order

1. Active tenant template for event and channel.
2. Active global default for event and channel.
3. Fail with `MissingNotificationTemplateException`.

## Variables

Variables use `{{key}}` syntax. Required variables are stored in the template `variables` JSON column and validated before delivery records are created.
