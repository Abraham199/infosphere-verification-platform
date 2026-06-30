<?php

namespace App\Domain\Reporting\Models;

use App\Domain\Reporting\Enums\ReportFrequency;
use App\Domain\Reporting\Enums\ReportStatus;
use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportingJob extends Model
{
    use HasUuid;

    protected $fillable = ['tenant_id', 'report_key', 'frequency', 'status', 'parameters', 'scheduled_for', 'started_at', 'completed_at', 'failed_at', 'failure_reason'];

    protected function casts(): array
    {
        return [
            'frequency' => ReportFrequency::class,
            'status' => ReportStatus::class,
            'parameters' => 'array',
            'scheduled_for' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function exports(): HasMany
    {
        return $this->hasMany(ReportingExport::class);
    }
}
