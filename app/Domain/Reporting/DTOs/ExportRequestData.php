<?php

namespace App\Domain\Reporting\DTOs;

use App\Domain\Reporting\Enums\ExportFormat;

class ExportRequestData
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        public readonly string $reportKey,
        public readonly ExportFormat $format,
        public readonly ?string $tenantId = null,
        public readonly array $parameters = [],
        public readonly ?string $dedupeKey = null,
    ) {
    }
}
