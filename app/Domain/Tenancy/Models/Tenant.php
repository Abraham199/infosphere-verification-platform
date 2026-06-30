<?php

namespace App\Domain\Tenancy\Models;

use App\Domain\Tenancy\Events\TenantCreated;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'legal_name',
        'email',
        'phone',
        'status',
        'plan_code',
        'default_currency',
        'timezone',
    ];

    protected $dispatchesEvents = [
        'created' => TenantCreated::class,
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function branding(): HasOne
    {
        return $this->hasOne(TenantBranding::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomain::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(TenantSetting::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
