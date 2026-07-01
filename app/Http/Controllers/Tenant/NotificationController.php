<?php

namespace App\Http\Controllers\Tenant;

use App\Domain\Tenancy\Services\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpdateNotificationPreferencesRequest;
use App\Services\NotificationCenterWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly NotificationCenterWorkflowService $notifications,
    ) {
    }

    public function index(Request $request): View
    {
        return $this->inbox($request, null);
    }

    public function unread(Request $request): View
    {
        return $this->inbox($request, 'unread');
    }

    public function read(Request $request): View
    {
        return $this->inbox($request, 'read');
    }

    public function archived(Request $request): View
    {
        return $this->inbox($request, 'archived');
    }

    public function show(string $tenantSlug, string $notification): View
    {
        $tenant = $this->tenantContext->require();

        return view('tenant.notifications.show', [
            'tenant' => $tenant,
            'notification' => $this->notifications->tenantNotification($tenant->id, request()->user()->id, $notification),
        ]);
    }

    public function markRead(string $tenantSlug, string $notification): RedirectResponse
    {
        $tenant = $this->tenantContext->require();
        $notificationModel = $this->notifications->tenantNotification($tenant->id, request()->user()->id, $notification);
        $this->notifications->markRead($notificationModel);

        return redirect()
            ->route('tenant.notifications.show', ['tenant' => $tenant, 'notification' => $notificationModel->id])
            ->with('status', 'Notification marked as read.');
    }

    public function archive(string $tenantSlug, string $notification): RedirectResponse
    {
        $tenant = $this->tenantContext->require();
        $notificationModel = $this->notifications->tenantNotification($tenant->id, request()->user()->id, $notification);
        $this->notifications->archive($notificationModel, request()->user()->id);

        return redirect()
            ->route('tenant.notifications.index', ['tenant' => $tenant])
            ->with('status', 'Notification archived.');
    }

    public function preferences(): View
    {
        $tenant = $this->tenantContext->require();

        return view('tenant.notifications.preferences', [
            'tenant' => $tenant,
            'types' => NotificationCenterWorkflowService::TYPES,
            'channels' => $this->notifications->preferenceChannels(),
            'preferences' => $this->notifications->preferenceRows($tenant->id, request()->user()->id),
        ]);
    }

    public function updatePreferences(UpdateNotificationPreferencesRequest $request): RedirectResponse
    {
        $tenant = $this->tenantContext->require();

        $this->notifications->updatePreferences(
            tenantId: $tenant->id,
            userId: $request->user()->id,
            preferences: $request->validated('preferences', []),
        );

        return redirect()
            ->route('tenant.notifications.preferences', ['tenant' => $tenant])
            ->with('status', 'Notification preferences updated.');
    }

    private function inbox(Request $request, ?string $scope): View
    {
        $tenant = $this->tenantContext->require();
        $filters = collect($request->only(['event_type']))
            ->filter(fn ($value): bool => is_string($value) && $value !== '')
            ->all();

        if ($scope !== null) {
            $filters['scope'] = $scope;
        }

        return view('tenant.notifications.index', [
            'tenant' => $tenant,
            'notifications' => $this->notifications->tenantInbox($tenant->id, $request->user()->id, $filters),
            'filters' => $filters,
            'types' => NotificationCenterWorkflowService::TYPES,
            'scope' => $scope,
        ]);
    }
}
