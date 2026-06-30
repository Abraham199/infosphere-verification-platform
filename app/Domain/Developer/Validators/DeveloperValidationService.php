<?php

namespace App\Domain\Developer\Validators;

use App\Domain\Developer\DTOs\RateLimitRuleData;
use App\Domain\Developer\DTOs\WebhookEndpointData;
use App\Domain\Developer\Exceptions\InvalidWebhookException;
use App\Domain\Developer\Exceptions\RateLimitExceededException;
use App\Domain\Developer\Interfaces\DeveloperRepositoryInterface;

class DeveloperValidationService
{
    public function __construct(private readonly DeveloperRepositoryInterface $developers)
    {
    }

    public function validateWebhookEndpoint(WebhookEndpointData $data): void
    {
        if (filter_var($data->url, FILTER_VALIDATE_URL) === false || ! str_starts_with($data->url, 'https://')) {
            throw new InvalidWebhookException('Webhook endpoint must be a valid HTTPS URL.');
        }

        if ($data->events === []) {
            throw new InvalidWebhookException('Webhook endpoint requires at least one subscribed event.');
        }

        if ($this->developers->webhookEndpointExists($data->tenantId, $data->url, $data->environment->value)) {
            throw new InvalidWebhookException('Duplicate webhook endpoint registration.');
        }
    }

    public function validateRateLimitRule(RateLimitRuleData $data): void
    {
        if ($data->sustainedLimit <= 0 || $data->burstLimit <= 0 || $data->windowSeconds <= 0) {
            throw new RateLimitExceededException('Rate limit values must be positive.');
        }
    }
}
