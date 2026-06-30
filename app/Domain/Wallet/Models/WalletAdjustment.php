<?php

namespace App\Domain\Wallet\Models;

use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Wallet\Enums\TransactionStatus;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletAdjustment extends Model
{
    use HasUuid;

    protected $fillable = [
        'tenant_id',
        'wallet_id',
        'wallet_transaction_id',
        'reference',
        'direction',
        'amount',
        'currency',
        'reason',
        'requested_by',
        'approved_by',
        'approved_at',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'status' => TransactionStatus::class,
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'wallet_transaction_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
