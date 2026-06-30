<?php

namespace App\Domain\Ledger\Models;

use App\Domain\Ledger\Enums\ReconciliationStatus;
use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReconciliationResult extends Model
{
    use HasUuid;

    protected $fillable = [
        'tenant_id',
        'reconciliation_run_id',
        'source_type',
        'source_reference',
        'status',
        'expected_amount',
        'actual_amount',
        'difference_amount',
        'currency',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReconciliationStatus::class,
            'expected_amount' => 'decimal:2',
            'actual_amount' => 'decimal:2',
            'difference_amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(ReconciliationRun::class, 'reconciliation_run_id');
    }
}
