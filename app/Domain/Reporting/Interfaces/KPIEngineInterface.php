<?php

namespace App\Domain\Reporting\Interfaces;

use App\Domain\Reporting\DTOs\KPIQueryData;
use App\Domain\Reporting\Models\ReportingMetric;

interface KPIEngineInterface
{
    public function calculate(KPIQueryData $query): ReportingMetric;
}
