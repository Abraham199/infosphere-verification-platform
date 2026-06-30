<?php

namespace App\Domain\Developer\Models;

use App\Domain\Developer\Enums\DeveloperEnvironment;
use App\Domain\Developer\Enums\WebhookStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebhookEndpoint extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $fillable = ['tenant_id', 'api_client_id', 'environment', 'url', 'secret_hash', 'subscribed_events', 'status', 'last_success_at', 'last_failure_at'];

    protected function casts(): array
    {
        return [
            'environment' => DeveloperEnvironment::class,
            'subscribed_events' => 'array',
            'status' => WebhookStatus::class,
            'last_success_at' => 'datetime',
            'last_failure_at' => 'datetime',
        ];
    }

    public function deliveries(): HasMany { return $this->hasMany(WebhookDelivery::class); }
}
