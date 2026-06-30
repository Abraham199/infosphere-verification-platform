<?php

namespace App\Domain\Developer\Services;

use App\Domain\Developer\DTOs\ApiRequestContext;
use App\Domain\Developer\Interfaces\ApiUsageTrackerInterface;
use App\Domain\Developer\Interfaces\DeveloperRepositoryInterface;
use App\Domain\Developer\Models\ApiUsageLog;
use App\Domain\Reporting\DTOs\AnalyticsEventData;
use App\Domain\Reporting\Enums\AnalyticsSourceDomain;
use App\Domain\Reporting\Interfaces\AnalyticsCollectorInterface;

class ApiUsageTracker implements ApiUsageTrackerInterface
{
    public function __construct(
        private readonly DeveloperRepositoryInterface $developers,
        private readonly AnalyticsCollectorInterface $analytics,
    ) {
    }

    public function track(ApiRequestContext $context): ApiUsageLog
    {
        $log = $this->developers->createUsageLog([
            'tenant_id' => $context->tenantId,
            'api_client_id' => $context->apiClientId,
            'request_id' => $context->requestId,
            'api_version' => $context->apiVersion,
            'method' => $context->method,
            'endpoint' => $context->endpoint,
            'status_code' => $context->statusCode,
            'latency_ms' => $context->latencyMs,
            'rate_limited' => $context->rateLimited,
            'product_code' => $context->productCode,
            'error_code' => $context->errorCode,
            'metadata' => $context->metadata,
        ]);

        $this->analytics->collect(new AnalyticsEventData(
            eventName: 'api_request_received',
            sourceDomain: AnalyticsSourceDomain::SYSTEM,
            eventReference: 'api#'.$context->requestId,
            occurredAt: now(),
            tenantId: $context->tenantId,
            dimensions: [
                'api_version' => $context->apiVersion,
                'endpoint' => $context->endpoint,
                'status_code' => $context->statusCode,
                'product_code' => $context->productCode,
            ],
            measures: [
                'count' => 1,
                'latency_ms' => $context->latencyMs ?? 0,
                'rate_limited' => $context->rateLimited ? 1 : 0,
            ],
        ));

        return $log;
    }
}
