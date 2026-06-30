<?php

namespace App\Domain\Reporting\Interfaces;

use App\Domain\Reporting\DTOs\ReportRequestData;
use App\Domain\Reporting\Models\ReportingSnapshot;

interface ReportGeneratorInterface
{
    public function generate(ReportRequestData $request): ReportingSnapshot;
}
