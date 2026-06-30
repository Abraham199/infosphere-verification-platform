<?php

namespace App\Providers;

use App\Domain\Tenancy\Events\TenantCreated;
use App\Domain\Notification\Events\NotificationFailed;
use App\Domain\Notification\Events\NotificationPreferenceUpdated;
use App\Domain\Notification\Events\NotificationQueued;
use App\Domain\Notification\Events\NotificationRetried;
use App\Domain\Notification\Events\NotificationSent;
use App\Domain\Reporting\Events\AnalyticsCollected;
use App\Domain\Reporting\Events\ExportCreated;
use App\Domain\Reporting\Events\KPIUpdated;
use App\Domain\Reporting\Events\ReportGenerated;
use App\Domain\Developer\Events\ApiKeyCreated;
use App\Domain\Developer\Events\ApiKeyRevoked;
use App\Domain\Developer\Events\ApiRateLimitExceeded;
use App\Domain\Developer\Events\ApiRequestReceived;
use App\Domain\Developer\Events\SandboxApplicationCreated;
use App\Domain\Developer\Events\WebhookDelivered;
use App\Domain\Developer\Events\WebhookFailed;
use App\Domain\Support\Events\KnowledgeArticlePublished;
use App\Domain\Support\Events\SLAExceeded;
use App\Domain\Support\Events\TicketAssigned;
use App\Domain\Support\Events\TicketClosed;
use App\Domain\Support\Events\TicketCreated;
use App\Domain\Support\Events\TicketEscalated;
use App\Domain\Support\Events\TicketResolved;
use App\Domain\Support\Events\TicketUpdated;
use App\Events\PaymentFailed;
use App\Events\PaymentSucceeded;
use App\Events\RefundIssued;
use App\Events\VerificationCompleted;
use App\Events\WalletDebited;
use App\Events\WalletFunded;
use App\Listeners\CollectAnalyticsEvent;
use App\Listeners\LogDomainEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TenantCreated::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        WalletFunded::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        WalletDebited::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        VerificationCompleted::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        PaymentSucceeded::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        PaymentFailed::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        RefundIssued::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        NotificationQueued::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        NotificationSent::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        NotificationFailed::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        NotificationRetried::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        NotificationPreferenceUpdated::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        \App\Domain\Wallet\Events\WalletCredited::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        \App\Domain\Wallet\Events\WalletDebited::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        \App\Domain\Wallet\Events\WalletRefunded::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        \App\Domain\Ledger\Events\LedgerEntryPosted::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        \App\Domain\Ledger\Events\LedgerBatchCompleted::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        \App\Domain\Payment\Events\PaymentInitialized::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        \App\Domain\Payment\Events\PaymentSucceeded::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        \App\Domain\Payment\Events\PaymentFailed::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        \App\Domain\Payment\Events\PaymentWalletCredited::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        \App\Domain\Verification\Events\VerificationRequested::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        \App\Domain\Verification\Events\VerificationCompleted::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        \App\Domain\Verification\Events\VerificationFailed::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        \App\Domain\Product\Events\ProductCreated::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        \App\Domain\Product\Events\ProductPriceChanged::class => [
            LogDomainEvent::class,
            CollectAnalyticsEvent::class,
        ],
        AnalyticsCollected::class => [
            LogDomainEvent::class,
        ],
        KPIUpdated::class => [
            LogDomainEvent::class,
        ],
        ReportGenerated::class => [
            LogDomainEvent::class,
        ],
        ExportCreated::class => [
            LogDomainEvent::class,
        ],
        TicketCreated::class => [
            LogDomainEvent::class,
        ],
        TicketAssigned::class => [
            LogDomainEvent::class,
        ],
        TicketUpdated::class => [
            LogDomainEvent::class,
        ],
        TicketEscalated::class => [
            LogDomainEvent::class,
        ],
        TicketResolved::class => [
            LogDomainEvent::class,
        ],
        TicketClosed::class => [
            LogDomainEvent::class,
        ],
        SLAExceeded::class => [
            LogDomainEvent::class,
        ],
        KnowledgeArticlePublished::class => [
            LogDomainEvent::class,
        ],
        ApiKeyCreated::class => [
            LogDomainEvent::class,
        ],
        ApiKeyRevoked::class => [
            LogDomainEvent::class,
        ],
        ApiRequestReceived::class => [
            LogDomainEvent::class,
        ],
        ApiRateLimitExceeded::class => [
            LogDomainEvent::class,
        ],
        WebhookDelivered::class => [
            LogDomainEvent::class,
        ],
        WebhookFailed::class => [
            LogDomainEvent::class,
        ],
        SandboxApplicationCreated::class => [
            LogDomainEvent::class,
        ],
    ];
}
