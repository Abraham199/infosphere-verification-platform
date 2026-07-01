<?php

namespace App\Http\Controllers\Platform;

use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Enums\NotificationDeliveryStatus;
use App\Domain\Notification\Enums\NotificationStatus;
use App\Http\Controllers\Controller;
use App\Services\NotificationCenterWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationCenterWorkflowService $notifications)
    {
    }

    public function index(Request $request): View
    {
        return view('platform.notifications.index', [
            ...$this->notifications->platformDashboard($this->filters($request)),
            'filters' => $this->filters($request),
            'types' => NotificationCenterWorkflowService::TYPES,
            'statuses' => NotificationStatus::cases(),
            'deliveryStatuses' => NotificationDeliveryStatus::cases(),
            'channels' => NotificationChannel::cases(),
        ]);
    }

    public function queue(Request $request): View
    {
        return view('platform.notifications.queue', [
            'deliveries' => $this->notifications->platformDeliveries($this->filters($request)),
            'filters' => $this->filters($request),
            'types' => NotificationCenterWorkflowService::TYPES,
            'deliveryStatuses' => NotificationDeliveryStatus::cases(),
            'channels' => NotificationChannel::cases(),
        ]);
    }

    public function failed(Request $request): View
    {
        $filters = [...$this->filters($request), 'status' => NotificationDeliveryStatus::FAILED->value];

        return view('platform.notifications.failed', [
            'deliveries' => $this->notifications->platformDeliveries($filters),
            'filters' => $filters,
            'types' => NotificationCenterWorkflowService::TYPES,
            'channels' => NotificationChannel::cases(),
        ]);
    }

    public function show(string $notification): View
    {
        return view('platform.notifications.show', [
            'notification' => $this->notifications->platformNotification($notification),
        ]);
    }

    public function delivery(string $delivery): View
    {
        return view('platform.notifications.delivery', [
            'delivery' => $this->notifications->platformDelivery($delivery),
        ]);
    }

    public function retry(string $delivery): RedirectResponse
    {
        $deliveryModel = $this->notifications->platformDelivery($delivery);
        $this->notifications->retry($deliveryModel);

        return redirect()
            ->route('platform.notifications.delivery', ['delivery' => $deliveryModel->id])
            ->with('status', 'Notification delivery queued for retry.');
    }

    /**
     * @return array<string, string>
     */
    private function filters(Request $request): array
    {
        return collect($request->only(['status', 'event_type', 'channel']))
            ->filter(fn ($value): bool => is_string($value) && $value !== '')
            ->all();
    }
}
