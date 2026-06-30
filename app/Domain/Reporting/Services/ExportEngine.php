<?php

namespace App\Domain\Reporting\Services;

use App\Domain\Reporting\DTOs\ExportRequestData;
use App\Domain\Reporting\Enums\ExportStatus;
use App\Domain\Reporting\Events\ExportCreated;
use App\Domain\Reporting\Interfaces\ExportEngineInterface;
use App\Domain\Reporting\Interfaces\ReportingRepositoryInterface;
use App\Domain\Reporting\Models\ReportingExport;
use App\Domain\Reporting\Validators\ReportingValidationService;
use Illuminate\Support\Str;

class ExportEngine implements ExportEngineInterface
{
    public function __construct(
        private readonly ReportingRepositoryInterface $reports,
        private readonly ReportingValidationService $validator,
    ) {
    }

    public function request(ExportRequestData $request): ReportingExport
    {
        $reference = $request->dedupeKey ?? 'EXP-'.strtoupper((string) Str::ulid());
        $this->validator->validateExportRequest($request, $reference);

        $export = $this->reports->createExport([
            'tenant_id' => $request->tenantId,
            'report_key' => $request->reportKey,
            'format' => $request->format,
            'status' => ExportStatus::PENDING,
            'export_reference' => $reference,
            'parameters' => $request->parameters,
            'requested_at' => now(),
        ]);

        event(new ExportCreated($export));

        return $export;
    }
}
