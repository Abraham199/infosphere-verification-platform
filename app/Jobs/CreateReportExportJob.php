<?php

namespace App\Jobs;

use App\Domain\Reporting\DTOs\ExportRequestData;
use App\Domain\Reporting\Interfaces\ExportEngineInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateReportExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly ExportRequestData $request)
    {
    }

    public function handle(ExportEngineInterface $exports): void
    {
        $exports->request($this->request);
    }
}
