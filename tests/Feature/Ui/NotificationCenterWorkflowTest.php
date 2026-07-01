<?php

namespace Tests\Feature\Ui;

use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Notification\Enums\NotificationCategory;
use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Enums\NotificationDeliveryStatus;
use App\Domain\Notification\Enums\NotificationStatus;
use App\Domain\Notification\Models\Notification;
use App\Domain\Notification\Models\NotificationDelivery;
use App\Domain\Notification\Models\NotificationPreference;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Services\TenantContext;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NotificationCenterWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_inbox_is_tenant_isolated(): void
    {
        [$tenant, $user] = $this->tenantFixture();
        [$otherTenant, $otherUser] = $this->tenantFixture('notification-other');
        $visible = $this->notification($tenant, $user, 'wallet.funded', 'Wallet funded');
        $hidden = $this->notification($otherTenant, $otherUser, 'wallet.debited', 'Other wallet debited');

        $this->actingAs($user)
            ->get(route('tenant.notifications.index', ['tenant' => $tenant]))
            ->assertOk()
            ->assertSee($visible->subject)
            ->assertDontSee($hidden->subject);

        $this->actingAs($user)
            ->get(route('tenant.notifications.show', ['tenant' => $tenant, 'notification' => $hidden->id]))
            ->assertNotFound();
    }

    public function test_notification_routes_respect_rbac(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Blocked Notification Tenant',
            'slug' => 'blocked-notification-'.Str::lower((string) Str::ulid()),
            'status' => 'active',
        ]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('tenant.notifications.index', ['tenant' => $tenant]))
            ->assertForbidden();
    }

    public function test_tenant_can_mark_notification_read_and_filter_unread_read(): void
    {
        [$tenant, $user] = $this->tenantFixture();
        $notification = $this->notification($tenant, $user, 'verification.completed', 'Verification completed');
        $this->notification($tenant, $user, 'verification.failed', 'Verification failed', read: true);

        $this->actingAs($user)
            ->get(route('tenant.notifications.unread', ['tenant' => $tenant]))
            ->assertOk()
            ->assertSee('Verification completed')
            ->assertDontSee('Verification failed');

        $this->actingAs($user)
            ->patch(route('tenant.notifications.read.update', ['tenant' => $tenant, 'notification' => $notification->id]))
            ->assertRedirect(route('tenant.notifications.show', ['tenant' => $tenant, 'notification' => $notification->id]));

        $this->assertNotNull($notification->refresh()->read_at);
        $this->assertSame(NotificationStatus::READ, $notification->status);

        $this->actingAs($user)
            ->get(route('tenant.notifications.read', ['tenant' => $tenant]))
            ->assertOk()
            ->assertSee('Verification completed');
    }

    public function test_tenant_can_archive_notification(): void
    {
        [$tenant, $user] = $this->tenantFixture();
        $notification = $this->notification($tenant, $user, 'ticket.closed', 'Ticket closed');

        $this->actingAs($user)
            ->patch(route('tenant.notifications.archive', ['tenant' => $tenant, 'notification' => $notification->id]))
            ->assertRedirect(route('tenant.notifications.index', ['tenant' => $tenant]));

        $this->assertNotNull($notification->refresh()->data['archived_at'] ?? null);

        $this->actingAs($user)
            ->get(route('tenant.notifications.index', ['tenant' => $tenant]))
            ->assertOk()
            ->assertDontSee('Ticket closed');

        $this->actingAs($user)
            ->get(route('tenant.notifications.archived', ['tenant' => $tenant]))
            ->assertOk()
            ->assertSee('Ticket closed');
    }

    public function test_notification_filtering_by_type(): void
    {
        [$tenant, $user] = $this->tenantFixture();
        $this->notification($tenant, $user, 'payment.successful', 'Payment successful');
        $this->notification($tenant, $user, 'product.suspended', 'Product suspended');

        $this->actingAs($user)
            ->get(route('tenant.notifications.index', ['tenant' => $tenant, 'event_type' => 'payment.successful']))
            ->assertOk()
            ->assertSee('Payment successful')
            ->assertDontSee('Product suspended');
    }

    public function test_platform_queue_access_and_delivery_status(): void
    {
        [$tenant, $user] = $this->tenantFixture();
        $notification = $this->notification($tenant, $user, 'wallet.funded', 'Wallet funded');
        $delivery = $this->delivery($notification, NotificationDeliveryStatus::SENT);
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->get(route('platform.notifications.queue', ['status' => 'sent']))
            ->assertOk()
            ->assertSee($delivery->delivery_reference)
            ->assertSee('Sent');

        $this->actingAs($admin)
            ->get(route('platform.notifications.delivery', ['delivery' => $delivery->id]))
            ->assertOk()
            ->assertSee('Delivery Detail')
            ->assertSee('Sent');
    }

    public function test_platform_can_retry_failed_delivery(): void
    {
        [$tenant, $user] = $this->tenantFixture();
        $notification = $this->notification($tenant, $user, 'payment.failed', 'Payment failed');
        $delivery = $this->delivery($notification, NotificationDeliveryStatus::FAILED, failureReason: 'SMTP timeout');
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->post(route('platform.notifications.delivery.retry', ['delivery' => $delivery->id]))
            ->assertRedirect(route('platform.notifications.delivery', ['delivery' => $delivery->id]));

        $this->assertSame(NotificationDeliveryStatus::RETRYING, $delivery->refresh()->status);
        $this->assertNotNull($delivery->next_retry_at);
    }

    public function test_tenant_can_update_preferences(): void
    {
        [$tenant, $user] = $this->tenantFixture();

        $this->actingAs($user)
            ->put(route('tenant.notifications.preferences.update', ['tenant' => $tenant]), [
                'preferences' => [
                    'ticket.created' => [
                        'email' => '0',
                        'in_app' => '1',
                    ],
                    'wallet.funded' => [
                        'email' => '0',
                    ],
                ],
            ])
            ->assertRedirect(route('tenant.notifications.preferences', ['tenant' => $tenant]));

        $this->assertDatabaseHas('notification_preferences', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'notification_type' => 'ticket.created',
            'channel' => 'email',
            'is_enabled' => false,
        ]);
        $this->assertDatabaseHas('notification_preferences', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'notification_type' => 'wallet.funded',
            'channel' => 'email',
            'is_enabled' => true,
        ]);
    }

    private function tenantFixture(string $slugPrefix = 'notification-workflow'): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $tenant = Tenant::query()->create([
            'name' => 'Notification Workflow Tenant',
            'slug' => $slugPrefix.'-'.Str::lower((string) Str::ulid()),
            'status' => 'active',
        ]);
        app(TenantContext::class)->set($tenant);

        $permissions = collect(['dashboard.view', 'notifications.view', 'notifications.preferences'])
            ->map(fn (string $name) => Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']));
        $role = Role::query()->create(['tenant_id' => $tenant->id, 'name' => 'Notification Operator '.$tenant->id, 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole($role);

        return [$tenant, $user];
    }

    private function superAdmin(): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        app(TenantContext::class)->clear();

        $role = Role::query()->firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['user_type' => 'platform']);
        $user->assignRole($role);

        return $user;
    }

    private function notification(Tenant $tenant, User $user, string $eventType, string $subject, bool $read = false): Notification
    {
        return Notification::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'event_type' => $eventType,
            'category' => NotificationCategory::SERVICE,
            'priority' => 'normal',
            'status' => $read ? NotificationStatus::READ : NotificationStatus::QUEUED,
            'subject' => $subject,
            'title' => $subject,
            'body' => $subject.' body',
            'data' => [],
            'queued_at' => now(),
            'read_at' => $read ? now() : null,
        ]);
    }

    private function delivery(Notification $notification, NotificationDeliveryStatus $status, ?string $failureReason = null): NotificationDelivery
    {
        return NotificationDelivery::query()->create([
            'notification_id' => $notification->id,
            'tenant_id' => $notification->tenant_id,
            'user_id' => $notification->user_id,
            'channel' => NotificationChannel::EMAIL,
            'recipient' => 'customer@example.test',
            'delivery_reference' => 'NTF-'.strtoupper((string) Str::ulid()),
            'status' => $status,
            'attempts' => $status === NotificationDeliveryStatus::FAILED ? 1 : 0,
            'max_attempts' => 3,
            'sent_at' => $status === NotificationDeliveryStatus::SENT ? now() : null,
            'failed_at' => $status === NotificationDeliveryStatus::FAILED ? now() : null,
            'failure_reason' => $failureReason,
        ]);
    }
}
