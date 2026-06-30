<?php

namespace App\Domain\Notification\Models;

use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Tenancy\Models\Tenant;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationChannelLog extends Model
{
    use HasUuid;

    protected $fillable = ['notification_delivery_id', 'tenant_id', 'channel', 'direction', 'status', 'request_payload', 'response_payload', 'error_message', 'duration_ms'];

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'request_payload' => 'array',
            'response_payload' => 'array',
            'duration_ms' => 'integer',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(NotificationDelivery::class, 'notification_delivery_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
