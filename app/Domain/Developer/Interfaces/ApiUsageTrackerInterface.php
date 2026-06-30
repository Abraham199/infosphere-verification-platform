<?php

namespace App\Domain\Developer\Interfaces;

use App\Domain\Developer\DTOs\ApiRequestContext;
use App\Domain\Developer\Models\ApiUsageLog;

interface ApiUsageTrackerInterface
{
    public function track(ApiRequestContext $context): ApiUsageLog;
}
