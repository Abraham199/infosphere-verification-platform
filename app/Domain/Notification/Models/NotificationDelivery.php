<?php

namespace App\Domain\Notification\Models;

use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Enums\NotificationDeliveryStatus;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationDelivery extends Model
{
    use HasUuid;

    protected $fillable = ['notification_id', 'tenant_id', 'user_id', 'channel', 'recipient', 'delivery_reference', 'status', 'attempts', 'max_attempts', 'next_retry_at', 'sent_at', 'failed_at', 'provider_message_id', 'failure_reason', 'metadata'];

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'status' => NotificationDeliveryStatus::class,
            'attempts' => 'integer',
            'max_attempts' => 'integer',
            'next_retry_at' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(NotificationChannelLog::class);
    }
}
