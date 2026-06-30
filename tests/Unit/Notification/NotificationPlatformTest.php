<?php

namespace Tests\Unit\Notification;

use App\Domain\Notification\DTOs\NotificationDeliveryResult;
use App\Domain\Notification\DTOs\NotificationMessageData;
use App\Domain\Notification\DTOs\NotificationRecipientData;
use App\Domain\Notification\Enums\NotificationCategory;
use App\Domain\Notification\Enums\NotificationChannel;
use App\Domain\Notification\Enums\NotificationDeliveryStatus;
use App\Domain\Notification\Enums\NotificationStatus;
use App\Domain\Notification\Events\NotificationQueued;
use App\Domain\Notification\Events\NotificationRetried;
use App\Domain\Notification\Interfaces\NotificationChannelAdapterInterface;
use App\Domain\Notification\Interfaces\NotificationChannelManagerInterface;
use App\Domain\Notification\Models\NotificationChannelLog;
use App\Domain\Notification\Models\NotificationDelivery;
use App\Domain\Notification\Models\NotificationPreference;
use App\Domain\Notification\Models\NotificationTemplate;
use App\Domain\Notification\Services\NotificationPreferenceService;
use App\Domain\Notification\Services\NotificationService;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_resolution_uses_global_default(): void
    {
        $this->createTemplate(null, 'wallet.funded', 'Wallet funded', 'Hello {{name}}, funded {{amount}}');

        $message = $this->message(eventType: 'wallet.funded', variables: ['name' => 'Ada', 'amount' => 'NGN 100']);
        $notification = app(NotificationService::class)->queue($message);

        $this->assertSame('Wallet funded', $notification->subject);
        $this->assertSame('Hello Ada, funded NGN 100', $notification->body);
        $this->assertSame(NotificationStatus::QUEUED, $notification->status);
    }

    public function test_tenant_template_override_wins_over_global_default(): void
    {
        $tenant = $this->tenant();
        $this->createTemplate(null, 'verification.completed', 'Global subject', 'Global body');
        $this->createTemplate($tenant->id, 'verification.completed', 'Tenant subject', 'Tenant body');

        $notification = app(NotificationService::class)->queue($this->message(
            tenantId: $tenant->id,
            eventType: 'verification.completed',
        ));

        $this->assertSame('Tenant subject', $notification->subject);
        $this->assertSame('Tenant body', $notification->body);
    }

    public function test_email_delivery_adapter_marks_delivery_sent_and_logs_channel_response(): void
    {
        Mail::fake();
        $this->createTemplate(null, 'payment.succeeded', 'Payment received', 'Payment success');
        $notification = app(NotificationService::class)->queue($this->message(eventType: 'payment.succeeded'));

        $delivery = app(NotificationService::class)->deliver($notification->deliveries()->first());

        $this->assertSame(NotificationDeliveryStatus::SENT, $delivery->status);
        $this->assertSame(1, $delivery->attempts);
        $this->assertSame(1, NotificationChannelLog::query()->where('notification_delivery_id', $delivery->id)->count());
    }

    public function test_optional_notification_respects_disabled_preference(): void
    {
        $tenant = $this->tenant();
        $this->createTemplate(null, 'product.marketing', 'Marketing', 'Offer');
        NotificationPreference::query()->create([
            'tenant_id' => $tenant->id,
            'notification_type' => 'product.marketing',
            'channel' => NotificationChannel::EMAIL,
            'category' => NotificationCategory::MARKETING,
            'is_enabled' => false,
            'is_mandatory' => false,
        ]);

        $notification = app(NotificationService::class)->queue($this->message(
            tenantId: $tenant->id,
            eventType: 'product.marketing',
            category: NotificationCategory::MARKETING,
        ));

        $this->assertSame(0, $notification->deliveries()->count());
    }

    public function test_mandatory_financial_notification_cannot_be_disabled(): void
    {
        $preference = NotificationPreference::query()->create([
            'notification_type' => 'wallet.debited',
            'channel' => NotificationChannel::EMAIL,
            'category' => NotificationCategory::FINANCIAL,
            'is_enabled' => true,
            'is_mandatory' => true,
        ]);

        app(NotificationPreferenceService::class)->update($preference, false);

        $this->assertTrue($preference->refresh()->is_enabled);
    }

    public function test_delivery_retry_tracking_sets_retry_state(): void
    {
        Event::fake([NotificationQueued::class, NotificationRetried::class]);
        $this->createTemplate(null, 'security.alert', 'Alert', 'Security alert');
        $notification = app(NotificationService::class)->queue($this->message(
            eventType: 'security.alert',
            category: NotificationCategory::SECURITY,
        ));

        $delivery = app(NotificationService::class)->retry($notification->deliveries()->first());

        $this->assertSame(NotificationDeliveryStatus::RETRYING, $delivery->status);
        $this->assertNotNull($delivery->next_retry_at);
        Event::assertDispatched(NotificationRetried::class);
    }

    public function test_failed_adapter_records_failure_and_delivery_log(): void
    {
        $this->app->bind(NotificationChannelManagerInterface::class, fn () => new class implements NotificationChannelManagerInterface {
            public function driver(NotificationChannel $channel): NotificationChannelAdapterInterface
            {
                return new class implements NotificationChannelAdapterInterface {
                    public function send(NotificationDelivery $delivery): NotificationDeliveryResult
                    {
                        return new NotificationDeliveryResult(false, failureReason: 'SMTP rejected message.');
                    }
                };
            }
        });

        $this->createTemplate(null, 'service.failed', 'Service failed', 'Failure notice');
        $notification = app(NotificationService::class)->queue($this->message(eventType: 'service.failed'));

        $delivery = app(NotificationService::class)->deliver($notification->deliveries()->first());

        $this->assertSame(NotificationDeliveryStatus::FAILED, $delivery->status);
        $this->assertSame('SMTP rejected message.', $delivery->failure_reason);
        $this->assertSame(1, NotificationChannelLog::query()->where('notification_delivery_id', $delivery->id)->count());
    }

    private function createTemplate(?string $tenantId, string $eventType, string $subject, string $body): NotificationTemplate
    {
        return NotificationTemplate::query()->create([
            'tenant_id' => $tenantId,
            'event_type' => $eventType,
            'channel' => NotificationChannel::EMAIL,
            'subject' => $subject,
            'body' => $body,
            'variables' => [],
            'status' => 'active',
            'version' => 1,
            'is_default' => $tenantId === null,
        ]);
    }

    private function message(
        ?string $tenantId = null,
        string $eventType = 'test.event',
        NotificationCategory $category = NotificationCategory::SERVICE,
        array $variables = [],
    ): NotificationMessageData {
        return new NotificationMessageData(
            eventType: $eventType,
            category: $category,
            recipients: [new NotificationRecipientData(NotificationChannel::EMAIL, 'customer@example.test')],
            variables: $variables,
            tenantId: $tenantId,
        );
    }

    private function tenant(): Tenant
    {
        return Tenant::query()->create([
            'name' => 'Notification Test Tenant',
            'slug' => 'notification-test-'.Str::lower((string) Str::ulid()),
            'status' => 'active',
        ]);
    }
}
