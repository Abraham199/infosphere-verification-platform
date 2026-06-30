<?php

namespace App\Domain\Reporting\Interfaces;

use App\Domain\Reporting\DTOs\ExportRequestData;
use App\Domain\Reporting\Models\ReportingExport;

interface ExportEngineInterface
{
    public function request(ExportRequestData $request): ReportingExport;
}
