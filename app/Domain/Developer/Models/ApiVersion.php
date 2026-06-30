<?php

namespace App\Domain\Developer\Models;

use App\Domain\Developer\Enums\ApiVersionStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ApiVersion extends Model
{
    use HasUuid;

    protected $fillable = ['version', 'status', 'is_default', 'released_at', 'deprecated_at', 'sunsets_at', 'metadata'];

    protected function casts(): array
    {
        return [
            'status' => ApiVersionStatus::class,
            'is_default' => 'boolean',
            'released_at' => 'datetime',
            'deprecated_at' => 'datetime',
            'sunsets_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
