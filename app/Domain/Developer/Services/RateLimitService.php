<?php

namespace App\Domain\Developer\Services;

use App\Domain\Developer\DTOs\ApiRequestContext;
use App\Domain\Developer\DTOs\RateLimitRuleData;
use App\Domain\Developer\Events\ApiRateLimitExceeded;
use App\Domain\Developer\Exceptions\RateLimitExceededException;
use App\Domain\Developer\Interfaces\DeveloperRepositoryInterface;
use App\Domain\Developer\Interfaces\RateLimitServiceInterface;
use App\Domain\Developer\Models\ApiRateLimit;
use App\Domain\Developer\Validators\DeveloperValidationService;

class RateLimitService implements RateLimitServiceInterface
{
    public function __construct(
        private readonly DeveloperRepositoryInterface $developers,
        private readonly DeveloperValidationService $validator,
    ) {
    }

    public function createRule(RateLimitRuleData $data): ApiRateLimit
    {
        $this->validator->validateRateLimitRule($data);

        return $this->developers->createRateLimit([
            'tenant_id' => $data->tenantId,
            'api_client_id' => $data->apiClientId,
            'product_code' => $data->productCode,
            'endpoint' => $data->endpoint,
            'sustained_limit' => $data->sustainedLimit,
            'burst_limit' => $data->burstLimit,
            'window_seconds' => $data->windowSeconds,
            'is_active' => true,
        ]);
    }

    public function assertAllowed(ApiRequestContext $context): void
    {
        $rule = $this->developers->findRateLimit($context->tenantId, $context->apiClientId, $context->endpoint, $context->productCode);

        if ($rule === null) {
            return;
        }

        $count = $this->developers->recentUsageCount($context->tenantId, $context->apiClientId, $context->endpoint, $rule->window_seconds);

        if ($count >= $rule->burst_limit || $count >= $rule->sustained_limit) {
            event(new ApiRateLimitExceeded($context));
            throw new RateLimitExceededException('API rate limit exceeded.');
        }
    }
}
