<?php

namespace App\Domain\Reporting\Interfaces;

use App\Domain\Reporting\DTOs\ScheduledReportData;
use App\Domain\Reporting\Models\ReportingJob;

interface ScheduledReportServiceInterface
{
    public function schedule(ScheduledReportData $schedule): ReportingJob;
}
