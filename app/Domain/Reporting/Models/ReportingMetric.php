<?php

namespace App\Domain\Reporting\Models;

use App\Domain\Reporting\Enums\MetricCategory;
use App\Domain\Reporting\Enums\MetricKey;
use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportingMetric extends Model
{
    use HasUuid;

    protected $fillable = ['tenant_id', 'metric_key', 'category', 'value', 'unit', 'period_date', 'period_type', 'dimensions', 'calculated_at'];

    protected function casts(): array
    {
        return [
            'metric_key' => MetricKey::class,
            'category' => MetricCategory::class,
            'value' => 'decimal:6',
            'period_date' => 'date',
            'dimensions' => 'array',
            'calculated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
