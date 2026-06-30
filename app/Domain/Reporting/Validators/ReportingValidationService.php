<?php

namespace App\Domain\Reporting\Validators;

use App\Domain\Reporting\DTOs\ExportRequestData;
use App\Domain\Reporting\DTOs\KPIQueryData;
use App\Domain\Reporting\DTOs\ReportRequestData;
use App\Domain\Reporting\Exceptions\DuplicateExportRequestException;
use App\Domain\Reporting\Exceptions\InvalidReportParameterException;
use App\Domain\Reporting\Interfaces\ReportingRepositoryInterface;

class ReportingValidationService
{
    public function __construct(private readonly ReportingRepositoryInterface $reports)
    {
    }

    public function validateKpiQuery(KPIQueryData $query): void
    {
        $this->validateDateRange($query->from, $query->to);
    }

    public function validateReportRequest(ReportRequestData $request): void
    {
        if (trim($request->reportKey) === '') {
            throw new InvalidReportParameterException('Report key is required.');
        }

        $this->validateDateRange($request->from, $request->to);
    }

    public function validateExportRequest(ExportRequestData $request, string $reference): void
    {
        if (trim($request->reportKey) === '') {
            throw new InvalidReportParameterException('Export report key is required.');
        }

        if ($this->reports->exportReferenceExists($reference)) {
            throw new DuplicateExportRequestException('Duplicate export request.');
        }
    }

    private function validateDateRange(\DateTimeInterface $from, \DateTimeInterface $to): void
    {
        if ($from > $to) {
            throw new InvalidReportParameterException('Report start date must be before or equal to end date.');
        }
    }
}
