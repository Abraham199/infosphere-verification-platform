<?php

namespace App\Domain\Developer\Models;

use App\Domain\Developer\Enums\WebhookDeliveryStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    use HasUuid;

    protected $fillable = ['webhook_event_id', 'webhook_endpoint_id', 'delivery_reference', 'status', 'attempts', 'max_attempts', 'next_retry_at', 'response_status', 'response_body', 'failure_reason', 'delivered_at'];

    protected function casts(): array
    {
        return [
            'status' => WebhookDeliveryStatus::class,
            'attempts' => 'integer',
            'max_attempts' => 'integer',
            'next_retry_at' => 'datetime',
            'response_status' => 'integer',
            'delivered_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo { return $this->belongsTo(WebhookEvent::class, 'webhook_event_id'); }
    public function endpoint(): BelongsTo { return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id'); }
}
