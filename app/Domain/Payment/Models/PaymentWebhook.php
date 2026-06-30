<?php

namespace App\Domain\Payment\Models;

use App\Domain\Payment\Enums\PaymentProvider;
use App\Domain\Payment\Enums\PaymentWebhookStatus;
use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentWebhook extends Model
{
    use HasUuid;

    protected $fillable = [
        'tenant_id',
        'payment_transaction_id',
        'provider',
        'event_type',
        'event_reference',
        'signature_hash',
        'status',
        'received_at',
        'processed_at',
        'payload',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'status' => PaymentWebhookStatus::class,
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }
}
