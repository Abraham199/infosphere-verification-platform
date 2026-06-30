<?php

namespace App\Domain\Developer\Models;

use App\Domain\Developer\Enums\ApiCredentialStatus;
use App\Domain\Developer\Enums\DeveloperEnvironment;
use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiClient extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $fillable = ['tenant_id', 'developer_application_id', 'client_id', 'name', 'environment', 'status', 'allowed_scopes', 'allowed_ips', 'last_used_at'];

    protected function casts(): array
    {
        return [
            'environment' => DeveloperEnvironment::class,
            'status' => ApiCredentialStatus::class,
            'allowed_scopes' => 'array',
            'allowed_ips' => 'array',
            'last_used_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function application(): BelongsTo { return $this->belongsTo(DeveloperApplication::class, 'developer_application_id'); }
    public function keys(): HasMany { return $this->hasMany(ApiKey::class); }
}
