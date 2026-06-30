<?php

namespace App\Domain\Reporting\Models;

use App\Domain\Reporting\Enums\ExportFormat;
use App\Domain\Reporting\Enums\ExportStatus;
use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportingExport extends Model
{
    use HasUuid;

    protected $fillable = ['tenant_id', 'reporting_job_id', 'report_key', 'format', 'status', 'export_reference', 'parameters', 'storage_disk', 'file_path', 'requested_at', 'completed_at', 'failed_at', 'failure_reason'];

    protected function casts(): array
    {
        return [
            'format' => ExportFormat::class,
            'status' => ExportStatus::class,
            'parameters' => 'array',
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(ReportingJob::class, 'reporting_job_id');
    }
}
