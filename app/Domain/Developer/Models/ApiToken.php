<?php

namespace App\Domain\Developer\Models;

use App\Domain\Developer\Enums\ApiCredentialStatus;
use App\Domain\Developer\Enums\TokenType;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiToken extends Model
{
    use HasUuid;

    protected $fillable = ['tenant_id', 'api_client_id', 'user_id', 'name', 'token_hash', 'token_type', 'status', 'scopes', 'expires_at', 'last_used_at', 'revoked_at'];

    protected function casts(): array
    {
        return [
            'token_type' => TokenType::class,
            'status' => ApiCredentialStatus::class,
            'scopes' => 'array',
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function client(): BelongsTo { return $this->belongsTo(ApiClient::class, 'api_client_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
