<?php

namespace App\Domain\Wallet\Models;

use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Wallet\Enums\ReservationStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletReservation extends Model
{
    use HasUuid;

    protected $fillable = [
        'tenant_id',
        'wallet_id',
        'wallet_transaction_id',
        'reference',
        'amount',
        'captured_amount',
        'released_amount',
        'currency',
        'status',
        'expires_at',
        'related_type',
        'related_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'captured_amount' => 'decimal:2',
            'released_amount' => 'decimal:2',
            'status' => ReservationStatus::class,
            'expires_at' => 'datetime',
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

    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
