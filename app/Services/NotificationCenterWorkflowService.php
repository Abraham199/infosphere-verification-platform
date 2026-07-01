<?php

namespace App\Services;

use App\Domain\Notification\Enums\NotificationCategory;
use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Enums\NotificationDeliveryStatus;
use App\Domain\Notification\Interfaces\NotificationServiceInterface;
use App\Domain\Notification\Models\Notification;
use App\Domain\Notification\Models\NotificationDelivery;
use App\Domain\Notification\Models\NotificationPreference;
use App\Domain\Notification\Services\NotificationPreferenceService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class NotificationCenterWorkflowService
{
    public const TYPES = [
        'ticket.created' => ['label' => 'Ticket Created', 'category' => NotificationCategory::SERVICE],
        'ticket.assigned' => ['label' => 'Ticket Assigned', 'category' => NotificationCategory::SERVICE],
        'ticket.updated' => ['label' => 'Ticket Updated', 'category' => NotificationCategory::SERVICE],
        'ticket.closed' => ['label' => 'Ticket Closed', 'category' => NotificationCategory::SERVICE],
        'wallet.funded' => ['label' => 'Wallet Funded', 'category' => NotificationCategory::FINANCIAL],
        'wallet.debited' => ['label' => 'Wallet Debited', 'category' => NotificationCategory::FINANCIAL],
        'payment.successful' => ['label' => 'Payment Successful', 'category' => NotificationCategory::FINANCIAL],
        'payment.failed' => ['label' => 'Payment Failed', 'category' => NotificationCategory::FINANCIAL],
        'verification.started' => ['label' => 'Verification Started', 'category' => NotificationCategory::SERVICE],
        'verification.completed' => ['label' => 'Verification Completed', 'category' => NotificationCategory::SERVICE],
        'verification.failed' => ['label' => 'Verification Failed', 'category' => NotificationCategory::SERVICE],
        'product.activated' => ['label' => 'Product Activated', 'category' => NotificationCategory::SERVICE],
        'product.suspended' => ['label' => 'Product Suspended', 'category' => NotificationCategory::SERVICE],
    ];

    public function __construct(
        private readonly NotificationServiceInterface $notifications,
        private readonly NotificationPreferenceService $preferences,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function platformDashboard(array $filters = []): array
    {
        return [
            'stats' => [
                'queued' => NotificationDelivery::query()->where('status', NotificationDeliveryStatus::PENDING)->count(),
                'sent' => NotificationDelivery::query()->where('status', NotificationDeliveryStatus::SENT)->count(),
                'failed' => NotificationDelivery::query()->where('status', NotificationDeliveryStatus::FAILED)->count(),
                'retrying' => NotificationDelivery::query()->where('status', NotificationDeliveryStatus::RETRYING)->count(),
            ],
            'notifications' => $this->platformNotifications($filters, 8),
            'deliveries' => $this->platformDeliveries($filters, 8),
        ];
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function platformNotifications(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Notification::query()
            ->with(['tenant', 'user', 'deliveries'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['event_type'] ?? null, fn ($query, $eventType) => $query->where('event_type', $eventType))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function platformDeliveries(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return NotificationDelivery::query()
            ->with(['notification.tenant', 'notification.user', 'logs'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['channel'] ?? null, fn ($query, $channel) => $query->where('channel', $channel))
            ->when($filters['event_type'] ?? null, fn ($query, $eventType) => $query->whereHas('notification', fn ($query) => $query->where('event_type', $eventType)))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function platformNotification(string $id): Notification
    {
        return Notification::query()
            ->with(['tenant', 'user', 'deliveries.logs'])
            ->whereKey($id)
            ->firstOrFail();
    }

    public function platformDelivery(string $id): NotificationDelivery
    {
        return NotificationDelivery::query()
            ->with(['notification.tenant', 'notification.user', 'logs'])
            ->whereKey($id)
            ->firstOrFail();
    }

    public function retry(NotificationDelivery $delivery): NotificationDelivery
    {
        return $this->notifications->retry($delivery);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function tenantInbox(string $tenantId, string $userId, array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return Notification::query()
            ->with('deliveries')
            ->where('tenant_id', $tenantId)
            ->where(function ($query) use ($userId): void {
                $query->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->when(($filters['scope'] ?? null) === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when(($filters['scope'] ?? null) === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->when(($filters['scope'] ?? null) !== 'archived', fn ($query) => $query->where(function ($query): void {
                $query->whereNull('data->archived_at');
            }))
            ->when(($filters['scope'] ?? null) === 'archived', fn ($query) => $query->whereNotNull('data->archived_at'))
            ->when($filters['event_type'] ?? null, fn ($query, $eventType) => $query->where('event_type', $eventType))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function tenantNotification(string $tenantId, string $userId, string $id): Notification
    {
        return Notification::query()
            ->with('deliveries.logs')
            ->whereKey($id)
            ->where('tenant_id', $tenantId)
            ->where(function ($query) use ($userId): void {
                $query->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->firstOrFail();
    }

    public function markRead(Notification $notification): Notification
    {
        if ($notification->read_at === null) {
            $notification->forceFill([
                'status' => 'read',
                'read_at' => now(),
            ])->save();
        }

        return $notification;
    }

    public function archive(Notification $notification, string $userId): Notification
    {
        $data = $notification->data ?? [];
        $data['archived_at'] = now()->toISOString();
        $data['archived_by'] = $userId;

        $notification->forceFill(['data' => $data])->save();

        return $notification;
    }

    public function preferenceRows(string $tenantId, string $userId): Collection
    {
        return NotificationPreference::query()
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->orderBy('notification_type')
            ->orderBy('channel')
            ->get();
    }

    /**
     * @param array<string, mixed> $preferences
     */
    public function updatePreferences(string $tenantId, string $userId, array $preferences): void
    {
        DB::transaction(function () use ($tenantId, $userId, $preferences): void {
            foreach (self::TYPES as $eventType => $definition) {
                foreach ($this->preferenceChannels() as $channel) {
                    $category = $definition['category'];
                    $preference = NotificationPreference::query()->firstOrCreate(
                        [
                            'tenant_id' => $tenantId,
                            'user_id' => $userId,
                            'notification_type' => $eventType,
                            'channel' => $channel,
                        ],
                        [
                            'category' => $category,
                            'is_enabled' => true,
                            'is_mandatory' => $category->isMandatory(),
                        ],
                    );

                    $enabled = (bool) ($preferences[$eventType][$channel->value] ?? false);
                    $this->preferences->update($preference, $enabled);
                }
            }
        });
    }

    /**
     * @return array<int, NotificationChannel>
     */
    public function preferenceChannels(): array
    {
        return [
            NotificationChannel::IN_APP,
            NotificationChannel::EMAIL,
            NotificationChannel::SMS,
            NotificationChannel::PUSH,
        ];
    }
}
