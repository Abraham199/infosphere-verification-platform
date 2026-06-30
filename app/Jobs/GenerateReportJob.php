<?php

namespace App\Jobs;

use App\Domain\Reporting\DTOs\ReportRequestData;
use App\Domain\Reporting\Interfaces\ReportGeneratorInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly ReportRequestData $request)
    {
    }

    public function handle(ReportGeneratorInterface $reports): void
    {
        $reports->generate($this->request);
    }
}
