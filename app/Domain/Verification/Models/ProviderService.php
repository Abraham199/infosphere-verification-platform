<?php

namespace App\Domain\Verification\Models;

use App\Domain\Verification\Enums\VerificationProvider;
use App\Domain\Verification\Enums\VerificationServiceStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderService extends Model
{
    use HasUuid;

    protected $fillable = [
        'verification_service_id',
        'provider',
        'provider_service_code',
        'status',
        'priority',
        'capabilities',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'provider' => VerificationProvider::class,
            'status' => VerificationServiceStatus::class,
            'capabilities' => 'array',
            'metadata' => 'array',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(VerificationService::class, 'verification_service_id');
    }
}
