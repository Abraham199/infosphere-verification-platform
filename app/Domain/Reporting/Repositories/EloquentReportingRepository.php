<?php

namespace App\Domain\Reporting\Repositories;

use App\Domain\Reporting\DTOs\AnalyticsEventData;
use App\Domain\Reporting\DTOs\KPIQueryData;
use App\Domain\Reporting\Interfaces\ReportingRepositoryInterface;
use App\Domain\Reporting\Models\AnalyticsEvent;
use App\Domain\Reporting\Models\ReportingAuditLog;
use App\Domain\Reporting\Models\ReportingExport;
use App\Domain\Reporting\Models\ReportingJob;
use App\Domain\Reporting\Models\ReportingMetric;
use App\Domain\Reporting\Models\ReportingSnapshot;
use Illuminate\Support\Collection;

class EloquentReportingRepository implements ReportingRepositoryInterface
{
    public function analyticsEventExists(string $eventReference): bool
    {
        return AnalyticsEvent::query()->where('event_reference', $eventReference)->exists();
    }

    public function createAnalyticsEvent(AnalyticsEventData $data): AnalyticsEvent
    {
        return AnalyticsEvent::query()->create([
            'tenant_id' => $data->tenantId,
            'event_name' => $data->eventName,
            'source_domain' => $data->sourceDomain,
            'event_reference' => $data->eventReference,
            'occurred_at' => $data->occurredAt,
            'dimensions' => $data->dimensions,
            'measures' => $data->measures,
            'payload' => $data->payload,
        ]);
    }

    public function sumEvents(KPIQueryData $query, string $measureKey, ?string $eventName = null): string
    {
        $builder = AnalyticsEvent::query()
            ->whereBetween('occurred_at', [$query->from, $query->to])
            ->when($query->tenantId, fn ($builder) => $builder->where('tenant_id', $query->tenantId))
            ->when($eventName, fn ($builder) => $builder->where('event_name', $eventName));

        return (string) $builder->sum("measures->{$measureKey}");
    }

    public function countEvents(KPIQueryData $query, ?string $eventName = null): int
    {
        return AnalyticsEvent::query()
            ->whereBetween('occurred_at', [$query->from, $query->to])
            ->when($query->tenantId, fn ($builder) => $builder->where('tenant_id', $query->tenantId))
            ->when($eventName, fn ($builder) => $builder->where('event_name', $eventName))
            ->count();
    }

    public function upsertMetric(array $attributes): ReportingMetric
    {
        return ReportingMetric::query()->updateOrCreate([
            'tenant_id' => $attributes['tenant_id'] ?? null,
            'metric_key' => $attributes['metric_key'],
            'period_date' => $attributes['period_date'],
            'period_type' => $attributes['period_type'],
        ], $attributes);
    }

    public function metrics(KPIQueryData $query): Collection
    {
        return ReportingMetric::query()
            ->where('metric_key', $query->metricKey->value)
            ->where('period_type', $query->periodType)
            ->whereBetween('period_date', [$query->from->toDateString(), $query->to->toDateString()])
            ->when($query->tenantId, fn ($builder) => $builder->where('tenant_id', $query->tenantId))
            ->orderBy('period_date')
            ->get();
    }

    public function createSnapshot(array $attributes): ReportingSnapshot
    {
        return ReportingSnapshot::query()->create($attributes);
    }

    public function createJob(array $attributes): ReportingJob
    {
        return ReportingJob::query()->create($attributes);
    }

    public function createExport(array $attributes): ReportingExport
    {
        return ReportingExport::query()->create($attributes);
    }

    public function exportReferenceExists(string $reference): bool
    {
        return ReportingExport::query()->where('export_reference', $reference)->exists();
    }

    public function createAuditLog(array $attributes): void
    {
        ReportingAuditLog::query()->create($attributes);
    }
}
