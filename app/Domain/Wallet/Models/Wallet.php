<?php

namespace App\Domain\Wallet\Models;

use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Wallet\Enums\WalletStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'currency',
        'available_balance',
        'pending_balance',
        'frozen_balance',
        'reserved_balance',
        'refund_balance',
        'status',
        'locked_at',
        'locked_reason',
    ];

    protected function casts(): array
    {
        return [
            'available_balance' => 'decimal:2',
            'pending_balance' => 'decimal:2',
            'frozen_balance' => 'decimal:2',
            'reserved_balance' => 'decimal:2',
            'refund_balance' => 'decimal:2',
            'status' => WalletStatus::class,
            'locked_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(WalletAccount::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(WalletReservation::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(WalletAdjustment::class);
    }
}
