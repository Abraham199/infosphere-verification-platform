<?php

namespace App\Domain\Reporting\Events;

use App\Domain\Reporting\Models\ReportingExport;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExportCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ReportingExport $export)
    {
    }
}
