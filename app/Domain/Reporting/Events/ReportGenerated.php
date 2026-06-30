<?php

namespace App\Domain\Reporting\Events;

use App\Domain\Reporting\Models\ReportingSnapshot;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportGenerated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ReportingSnapshot $snapshot)
    {
    }
}
