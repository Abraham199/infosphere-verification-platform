<?php

namespace App\Domain\Reporting\Models;

use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportingAuditLog extends Model
{
    use HasUuid;

    protected $fillable = ['tenant_id', 'user_id', 'action', 'auditable_type', 'auditable_id', 'metadata'];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
