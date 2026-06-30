<?php

namespace App\Domain\Reporting\Services;

use App\Domain\Reporting\DTOs\ScheduledReportData;
use App\Domain\Reporting\Enums\ReportStatus;
use App\Domain\Reporting\Interfaces\ReportingRepositoryInterface;
use App\Domain\Reporting\Interfaces\ScheduledReportServiceInterface;
use App\Domain\Reporting\Models\ReportingJob;

class ScheduledReportService implements ScheduledReportServiceInterface
{
    public function __construct(private readonly ReportingRepositoryInterface $reports)
    {
    }

    public function schedule(ScheduledReportData $schedule): ReportingJob
    {
        return $this->reports->createJob([
            'tenant_id' => $schedule->tenantId,
            'report_key' => $schedule->reportKey,
            'frequency' => $schedule->frequency,
            'status' => ReportStatus::PENDING,
            'parameters' => $schedule->parameters,
            'scheduled_for' => $schedule->scheduledFor,
        ]);
    }
}
