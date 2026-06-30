<?php

namespace App\Domain\Reporting\Models;

use App\Domain\Reporting\Enums\AnalyticsSourceDomain;
use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    use HasUuid;

    protected $fillable = ['tenant_id', 'event_name', 'source_domain', 'event_reference', 'occurred_at', 'dimensions', 'measures', 'payload'];

    protected function casts(): array
    {
        return [
            'source_domain' => AnalyticsSourceDomain::class,
            'occurred_at' => 'datetime',
            'dimensions' => 'array',
            'measures' => 'array',
            'payload' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
