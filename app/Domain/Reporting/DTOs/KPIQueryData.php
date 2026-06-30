<?php

namespace App\Domain\Reporting\DTOs;

use App\Domain\Reporting\Enums\MetricKey;
use Illuminate\Support\Carbon;

class KPIQueryData
{
    public function __construct(
        public readonly MetricKey $metricKey,
        public readonly Carbon $from,
        public readonly Carbon $to,
        public readonly ?string $tenantId = null,
        public readonly string $periodType = 'daily',
    ) {
    }
}
