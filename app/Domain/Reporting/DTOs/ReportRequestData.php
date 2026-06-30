<?php

namespace App\Domain\Reporting\DTOs;

use Illuminate\Support\Carbon;

class ReportRequestData
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        public readonly string $reportKey,
        public readonly Carbon $from,
        public readonly Carbon $to,
        public readonly ?string $tenantId = null,
        public readonly array $parameters = [],
    ) {
    }
}
