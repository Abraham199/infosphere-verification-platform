<?php

namespace App\Domain\Developer\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ApiUsageLog extends Model
{
    use HasUuid;

    protected $fillable = ['tenant_id', 'api_client_id', 'request_id', 'api_version', 'method', 'endpoint', 'status_code', 'latency_ms', 'rate_limited', 'product_code', 'error_code', 'metadata'];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'latency_ms' => 'integer',
            'rate_limited' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
