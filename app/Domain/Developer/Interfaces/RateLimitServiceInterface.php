<?php

namespace App\Domain\Developer\Interfaces;

use App\Domain\Developer\DTOs\ApiRequestContext;
use App\Domain\Developer\DTOs\RateLimitRuleData;
use App\Domain\Developer\Models\ApiRateLimit;

interface RateLimitServiceInterface
{
    public function createRule(RateLimitRuleData $data): ApiRateLimit;
    public function assertAllowed(ApiRequestContext $context): void;
}
