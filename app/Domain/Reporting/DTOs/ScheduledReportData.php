<?php

namespace App\Domain\Reporting\DTOs;

use App\Domain\Reporting\Enums\ReportFrequency;
use Illuminate\Support\Carbon;

class ScheduledReportData
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        public readonly string $reportKey,
        public readonly ReportFrequency $frequency,
        public readonly Carbon $scheduledFor,
        public readonly ?string $tenantId = null,
        public readonly array $parameters = [],
    ) {
    }
}
