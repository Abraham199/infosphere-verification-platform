<?php

namespace App\Domain\Verification\Models;

use App\Domain\Ledger\Models\LedgerBatch;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Verification\Enums\VerificationProvider;
use App\Domain\Verification\Enums\VerificationStatus;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\Models\WalletReservation;
use App\Domain\Wallet\Models\WalletTransaction;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VerificationRequest extends Model
{
    use HasUuid;

    protected $fillable = [
        'tenant_id',
        'wallet_id',
        'verification_service_id',
        'provider_service_id',
        'wallet_reservation_id',
        'wallet_transaction_id',
        'ledger_batch_id',
        'provider',
        'reference',
        'provider_reference',
        'idempotency_key',
        'price_charged',
        'currency',
        'status',
        'subject_identifier_hash',
        'request_payload',
        'submitted_at',
        'completed_at',
        'failed_at',
        'failure_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'provider' => VerificationProvider::class,
            'status' => VerificationStatus::class,
            'price_charged' => 'decimal:2',
            'request_payload' => 'array',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function wallet(): BelongsTo { return $this->belongsTo(Wallet::class); }
    public function service(): BelongsTo { return $this->belongsTo(VerificationService::class, 'verification_service_id'); }
    public function providerService(): BelongsTo { return $this->belongsTo(ProviderService::class); }
    public function reservation(): BelongsTo { return $this->belongsTo(WalletReservation::class, 'wallet_reservation_id'); }
    public function walletTransaction(): BelongsTo { return $this->belongsTo(WalletTransaction::class); }
    public function ledgerBatch(): BelongsTo { return $this->belongsTo(LedgerBatch::class); }
    public function result(): HasOne { return $this->hasOne(VerificationResult::class); }
}
