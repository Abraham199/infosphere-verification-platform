<?php

namespace App\Domain\Payment\Models;

use App\Domain\Payment\Enums\PaymentProvider;
use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProviderLog extends Model
{
    use HasUuid;

    protected $fillable = [
        'tenant_id',
        'payment_transaction_id',
        'provider',
        'direction',
        'action',
        'status',
        'http_status',
        'request_payload',
        'response_payload',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'request_payload' => 'array',
            'response_payload' => 'array',
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
