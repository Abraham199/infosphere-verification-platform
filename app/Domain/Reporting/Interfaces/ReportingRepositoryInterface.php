<?php

namespace App\Domain\Reporting\Interfaces;

use App\Domain\Reporting\DTOs\AnalyticsEventData;
use App\Domain\Reporting\DTOs\KPIQueryData;
use App\Domain\Reporting\Models\AnalyticsEvent;
use App\Domain\Reporting\Models\ReportingExport;
use App\Domain\Reporting\Models\ReportingJob;
use App\Domain\Reporting\Models\ReportingMetric;
use App\Domain\Reporting\Models\ReportingSnapshot;
use Illuminate\Support\Collection;

interface ReportingRepositoryInterface
{
    public function analyticsEventExists(string $eventReference): bool;
    public function createAnalyticsEvent(AnalyticsEventData $data): AnalyticsEvent;
    public function sumEvents(KPIQueryData $query, string $measureKey, ?string $eventName = null): string;
    public function countEvents(KPIQueryData $query, ?string $eventName = null): int;
    public function upsertMetric(array $attributes): ReportingMetric;
    public function metrics(KPIQueryData $query): Collection;
    public function createSnapshot(array $attributes): ReportingSnapshot;
    public function createJob(array $attributes): ReportingJob;
    public function createExport(array $attributes): ReportingExport;
    public function exportReferenceExists(string $reference): bool;
    public function createAuditLog(array $attributes): void;
}
