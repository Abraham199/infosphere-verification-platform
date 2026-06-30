<?php

namespace App\Domain\Support\Models;

use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportSlaPolicy extends Model
{
    use HasUuid;

    protected $fillable = ['tenant_id', 'support_priority_id', 'name', 'first_response_minutes', 'resolution_minutes', 'escalation_minutes', 'is_active', 'business_hours', 'metadata'];

    protected function casts(): array
    {
        return [
            'first_response_minutes' => 'integer',
            'resolution_minutes' => 'integer',
            'escalation_minutes' => 'integer',
            'is_active' => 'boolean',
            'business_hours' => 'array',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(SupportPriority::class, 'support_priority_id');
    }
}
