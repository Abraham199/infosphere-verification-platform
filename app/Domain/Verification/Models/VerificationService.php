<?php

namespace App\Domain\Verification\Models;

use App\Domain\Verification\Enums\VerificationServiceStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VerificationService extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'service_code',
        'description',
        'default_price',
        'global_price',
        'cost_price',
        'currency',
        'status',
        'supports_reservation',
        'required_fields',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:2',
            'global_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'status' => VerificationServiceStatus::class,
            'supports_reservation' => 'boolean',
            'required_fields' => 'array',
            'metadata' => 'array',
        ];
    }

    public function providerServices(): HasMany
    {
        return $this->hasMany(ProviderService::class);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(VerificationPricingRule::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(VerificationRequest::class);
    }
}
