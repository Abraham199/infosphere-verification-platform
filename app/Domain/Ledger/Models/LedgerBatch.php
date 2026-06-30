<?php

namespace App\Domain\Ledger\Models;

use App\Domain\Ledger\Enums\LedgerEntryStatus;
use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LedgerBatch extends Model
{
    use HasUuid;

    protected $fillable = [
        'tenant_id',
        'reference',
        'description',
        'currency',
        'total_debits',
        'total_credits',
        'status',
        'source_type',
        'source_id',
        'reversal_of_batch_id',
        'posted_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'total_debits' => 'decimal:2',
            'total_credits' => 'decimal:2',
            'status' => LedgerEntryStatus::class,
            'posted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function reversedBatch(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_batch_id');
    }
}
