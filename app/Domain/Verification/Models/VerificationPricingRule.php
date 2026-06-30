<?php

namespace App\Domain\Verification\Models;

use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Verification\Enums\VerificationServiceStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationPricingRule extends Model
{
    use HasUuid;

    protected $fillable = [
        'tenant_id',
        'verification_service_id',
        'price',
        'currency',
        'status',
        'starts_at',
        'ends_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'status' => VerificationServiceStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(VerificationService::class, 'verification_service_id');
    }
}
