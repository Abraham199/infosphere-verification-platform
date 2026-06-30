<?php

namespace App\Domain\Developer\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ApiRateLimit extends Model
{
    use HasUuid;

    protected $fillable = ['tenant_id', 'api_client_id', 'product_code', 'endpoint', 'sustained_limit', 'burst_limit', 'window_seconds', 'is_active', 'metadata'];

    protected function casts(): array
    {
        return [
            'sustained_limit' => 'integer',
            'burst_limit' => 'integer',
            'window_seconds' => 'integer',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
