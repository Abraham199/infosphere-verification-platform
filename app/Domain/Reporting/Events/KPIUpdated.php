<?php

namespace App\Domain\Reporting\Events;

use App\Domain\Reporting\Models\ReportingMetric;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KPIUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ReportingMetric $metric)
    {
    }
}
