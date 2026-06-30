<?php

namespace App\Domain\Reporting\Models;

use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportingSnapshot extends Model
{
    use HasUuid;

    protected $fillable = ['tenant_id', 'snapshot_key', 'period_type', 'period_start', 'period_end', 'data', 'generated_at'];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'data' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
