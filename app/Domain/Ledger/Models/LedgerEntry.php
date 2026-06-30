<?php

namespace App\Domain\Ledger\Models;

use App\Domain\Ledger\Enums\LedgerEntryStatus;
use App\Domain\Ledger\Enums\LedgerEntryType;
use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerEntry extends Model
{
    use HasUuid;

    protected $fillable = [
        'tenant_id',
        'ledger_batch_id',
        'ledger_account_id',
        'entry_reference',
        'type',
        'status',
        'amount',
        'currency',
        'account_balance_after',
        'reversal_of_entry_id',
        'posted_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => LedgerEntryType::class,
            'status' => LedgerEntryStatus::class,
            'amount' => 'decimal:2',
            'account_balance_after' => 'decimal:2',
            'posted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(LedgerBatch::class, 'ledger_batch_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'ledger_account_id');
    }

    public function reversedEntry(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_entry_id');
    }
}
