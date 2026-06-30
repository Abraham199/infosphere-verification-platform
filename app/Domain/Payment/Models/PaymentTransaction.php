<?php

namespace App\Domain\Payment\Models;

use App\Domain\Ledger\Models\LedgerBatch;
use App\Domain\Payment\Enums\PaymentMethod;
use App\Domain\Payment\Enums\PaymentProvider;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\Models\WalletTransaction;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentTransaction extends Model
{
    use HasUuid;

    protected $fillable = [
        'tenant_id',
        'wallet_id',
        'wallet_transaction_id',
        'ledger_batch_id',
        'provider',
        'reference',
        'provider_reference',
        'idempotency_key',
        'amount',
        'currency',
        'status',
        'payment_method',
        'authorization_url',
        'initialized_at',
        'verified_at',
        'paid_at',
        'failed_at',
        'failure_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'amount' => 'decimal:2',
            'initialized_at' => 'datetime',
            'verified_at' => 'datetime',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
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

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    public function ledgerBatch(): BelongsTo
    {
        return $this->belongsTo(LedgerBatch::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(PaymentWebhook::class);
    }

    public function providerLogs(): HasMany
    {
        return $this->hasMany(PaymentProviderLog::class);
    }
}
