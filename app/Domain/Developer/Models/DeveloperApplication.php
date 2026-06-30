<?php

namespace App\Domain\Developer\Models;

use App\Domain\Developer\Enums\ApiCredentialStatus;
use App\Domain\Developer\Enums\DeveloperEnvironment;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeveloperApplication extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $fillable = ['tenant_id', 'owner_id', 'name', 'slug', 'environment', 'status', 'redirect_uris', 'metadata'];

    protected function casts(): array
    {
        return [
            'environment' => DeveloperEnvironment::class,
            'status' => ApiCredentialStatus::class,
            'redirect_uris' => 'array',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_id'); }
    public function clients(): HasMany { return $this->hasMany(ApiClient::class); }
}
