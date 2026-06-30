<?php

namespace App\Domain\Developer\Models;

use App\Domain\Developer\Enums\ApiCredentialStatus;
use App\Domain\Developer\Enums\DeveloperEnvironment;
use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    use HasUuid;

    protected $fillable = ['tenant_id', 'api_client_id', 'name', 'key_prefix', 'key_hash', 'environment', 'status', 'scopes', 'expires_at', 'last_used_at', 'revoked_at', 'metadata'];

    protected function casts(): array
    {
        return [
            'environment' => DeveloperEnvironment::class,
            'status' => ApiCredentialStatus::class,
            'scopes' => 'array',
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function client(): BelongsTo { return $this->belongsTo(ApiClient::class, 'api_client_id'); }
}
