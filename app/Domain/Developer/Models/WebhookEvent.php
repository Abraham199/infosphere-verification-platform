<?php

namespace App\Domain\Developer\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookEvent extends Model
{
    use HasUuid;

    protected $fillable = ['tenant_id', 'event_type', 'event_reference', 'payload', 'occurred_at'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function deliveries(): HasMany { return $this->hasMany(WebhookDelivery::class); }
}
