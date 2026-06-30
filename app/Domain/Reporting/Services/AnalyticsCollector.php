<?php

namespace App\Domain\Reporting\Services;

use App\Domain\Reporting\DTOs\AnalyticsEventData;
use App\Domain\Reporting\Enums\AnalyticsSourceDomain;
use App\Domain\Reporting\Events\AnalyticsCollected;
use App\Domain\Reporting\Interfaces\AnalyticsCollectorInterface;
use App\Domain\Reporting\Interfaces\ReportingRepositoryInterface;
use App\Domain\Reporting\Models\AnalyticsEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AnalyticsCollector implements AnalyticsCollectorInterface
{
    public function __construct(private readonly ReportingRepositoryInterface $reports)
    {
    }

    public function collect(AnalyticsEventData $event): AnalyticsEvent
    {
        if ($this->reports->analyticsEventExists($event->eventReference)) {
            return AnalyticsEvent::query()->where('event_reference', $event->eventReference)->firstOrFail();
        }

        $analyticsEvent = $this->reports->createAnalyticsEvent($event);

        event(new AnalyticsCollected($analyticsEvent));

        return $analyticsEvent;
    }

    public function collectFromDomainEvent(object $event): ?AnalyticsEvent
    {
        $data = $this->mapDomainEvent($event);

        return $data === null ? null : $this->collect($data);
    }

    private function mapDomainEvent(object $event): ?AnalyticsEventData
    {
        $class = $event::class;
        $payload = $this->publicPayload($event);
        $eventName = Str::of(class_basename($class))->snake()->value();
        $sourceDomain = $this->sourceDomain($class);

        if ($sourceDomain === null) {
            return null;
        }

        return new AnalyticsEventData(
            eventName: $eventName,
            sourceDomain: $sourceDomain,
            eventReference: $this->eventReference($class, $payload),
            occurredAt: now(),
            tenantId: $this->tenantId($payload),
            dimensions: [
                'event_class' => $class,
                'status' => $this->status($payload),
                'provider' => $this->provider($payload),
                'product_code' => $this->productCode($payload),
            ],
            measures: [
                'amount' => $this->amount($payload),
                'duration_ms' => $this->duration($payload),
                'count' => 1,
            ],
            payload: $payload,
        );
    }

    private function sourceDomain(string $class): ?AnalyticsSourceDomain
    {
        return match (true) {
            str_contains($class, '\\Wallet\\') || str_contains($class, 'Wallet') => AnalyticsSourceDomain::WALLET,
            str_contains($class, '\\Ledger\\') || str_contains($class, 'Ledger') => AnalyticsSourceDomain::LEDGER,
            str_contains($class, '\\Payment\\') || str_contains($class, 'Payment') || str_contains($class, 'Refund') => AnalyticsSourceDomain::PAYMENT,
            str_contains($class, '\\Verification\\') || str_contains($class, 'Verification') => AnalyticsSourceDomain::VERIFICATION,
            str_contains($class, '\\Product\\') || str_contains($class, 'Product') => AnalyticsSourceDomain::PRODUCT,
            str_contains($class, '\\Notification\\') || str_contains($class, 'Notification') => AnalyticsSourceDomain::NOTIFICATION,
            str_contains($class, '\\Tenancy\\') || str_contains($class, 'Tenant') => AnalyticsSourceDomain::TENANT,
            default => null,
        };
    }

    private function publicPayload(object $event): array
    {
        return collect(get_object_vars($event))->map(function ($value) {
            if (is_object($value) && method_exists($value, 'getKey')) {
                return [
                    'id' => $value->getKey(),
                    'tenant_id' => $value->tenant_id ?? null,
                    'status' => $this->enumValue($value->status ?? null),
                    'amount' => $value->amount ?? $value->total_amount ?? null,
                    'provider' => $this->enumValue($value->provider ?? null),
                    'product_code' => $value->product_code ?? $value->code ?? null,
                    'created_at' => $value->created_at instanceof Carbon ? $value->created_at->toIso8601String() : null,
                ];
            }

            return $value;
        })->all();
    }

    private function eventReference(string $class, array $payload): string
    {
        $reference = data_get($payload, 'reference')
            ?? data_get($payload, 'payment.reference')
            ?? data_get($payload, 'request.reference')
            ?? data_get($payload, 'transaction.reference')
            ?? data_get($payload, 'walletTransaction.reference')
            ?? data_get($payload, '0.reference');

        return $reference !== null
            ? $class.'#'.$reference
            : $class.'#'.sha1(json_encode($payload, JSON_THROW_ON_ERROR));
    }

    private function tenantId(array $payload): ?string
    {
        return data_get($payload, 'tenantId')
            ?? data_get($payload, 'tenant_id')
            ?? data_get($payload, 'payment.tenant_id')
            ?? data_get($payload, 'request.tenant_id')
            ?? data_get($payload, 'transaction.tenant_id')
            ?? data_get($payload, 'delivery.tenant_id')
            ?? data_get($payload, '0.tenant_id');
    }

    private function amount(array $payload): float
    {
        return (float) (data_get($payload, 'amount')
            ?? data_get($payload, 'payment.amount')
            ?? data_get($payload, 'transaction.amount')
            ?? data_get($payload, 'walletTransaction.amount')
            ?? data_get($payload, '0.amount')
            ?? 0);
    }

    private function duration(array $payload): int
    {
        return (int) (data_get($payload, 'duration_ms') ?? data_get($payload, 'request.duration_ms') ?? 0);
    }

    private function status(array $payload): ?string
    {
        return data_get($payload, 'status') ?? data_get($payload, 'payment.status') ?? data_get($payload, 'request.status') ?? data_get($payload, 'transaction.status') ?? data_get($payload, 'delivery.status');
    }

    private function provider(array $payload): ?string
    {
        return data_get($payload, 'provider') ?? data_get($payload, 'payment.provider') ?? data_get($payload, 'request.provider');
    }

    private function productCode(array $payload): ?string
    {
        return data_get($payload, 'product_code') ?? data_get($payload, 'product.product_code') ?? data_get($payload, 'product.code');
    }

    private function enumValue(mixed $value): mixed
    {
        return $value instanceof \BackedEnum ? $value->value : $value;
    }
}
