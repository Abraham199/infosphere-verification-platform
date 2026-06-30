<?php

namespace App\Domain\Reporting\Enums;

enum ExportFormat: string
{
    case CSV = 'csv';
    case EXCEL = 'xlsx';
    case PDF = 'pdf';
}
