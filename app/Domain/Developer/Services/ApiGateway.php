<?php

namespace App\Domain\Developer\Services;

use App\Domain\Developer\DTOs\ApiRequestContext;
use App\Domain\Developer\Events\ApiRequestReceived;
use App\Domain\Developer\Interfaces\ApiGatewayInterface;
use App\Domain\Developer\Interfaces\ApiUsageTrackerInterface;
use App\Domain\Developer\Interfaces\ApiVersionResolverInterface;
use App\Domain\Developer\Interfaces\RateLimitServiceInterface;

class ApiGateway implements ApiGatewayInterface
{
    public function __construct(
        private readonly ApiVersionResolverInterface $versions,
        private readonly RateLimitServiceInterface $rateLimits,
        private readonly ApiUsageTrackerInterface $usage,
    ) {
    }

    public function accept(ApiRequestContext $context): ApiRequestContext
    {
        $this->versions->resolve($context->apiVersion);
        $this->rateLimits->assertAllowed($context);
        $this->usage->track($context);

        event(new ApiRequestReceived($context));

        return $context;
    }
}
