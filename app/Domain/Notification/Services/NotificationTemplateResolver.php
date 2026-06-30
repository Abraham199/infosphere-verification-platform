<?php

namespace App\Domain\Notification\Services;

use App\Domain\Notification\DTOs\ResolvedTemplateData;
use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Exceptions\MissingNotificationTemplateException;
use App\Domain\Notification\Interfaces\NotificationRepositoryInterface;
use App\Domain\Notification\Interfaces\NotificationTemplateResolverInterface;
use App\Domain\Notification\Validators\NotificationValidationService;

class NotificationTemplateResolver implements NotificationTemplateResolverInterface
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
        private readonly NotificationValidationService $validator,
    ) {
    }

    public function resolve(?string $tenantId, string $eventType, NotificationChannel $channel, array $variables = []): ResolvedTemplateData
    {
        $template = $this->notifications->findTemplate($tenantId, $eventType, $channel)
            ?? $this->notifications->findGlobalTemplate($eventType, $channel);

        if ($template === null) {
            throw new MissingNotificationTemplateException("No active notification template for [{$eventType}:{$channel->value}].");
        }

        $requiredVariables = $template->variables ?? [];
        $this->validator->validateVariables($requiredVariables, $variables);

        return new ResolvedTemplateData(
            template: $template,
            channel: $channel,
            subject: $this->render($template->subject, $variables),
            title: $this->render($template->title, $variables),
            body: $this->render($template->body, $variables) ?? '',
            variables: $variables,
        );
    }

    private function render(?string $content, array $variables): ?string
    {
        if ($content === null) {
            return null;
        }

        foreach ($variables as $key => $value) {
            $content = str_replace('{{'.$key.'}}', (string) $value, $content);
        }

        return $content;
    }
}
