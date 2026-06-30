<?php

namespace Tests\Unit\Reporting;

use App\Domain\Reporting\DTOs\AnalyticsEventData;
use App\Domain\Reporting\DTOs\ExportRequestData;
use App\Domain\Reporting\DTOs\KPIQueryData;
use App\Domain\Reporting\DTOs\ReportRequestData;
use App\Domain\Reporting\DTOs\ScheduledReportData;
use App\Domain\Reporting\Enums\AnalyticsSourceDomain;
use App\Domain\Reporting\Enums\ExportFormat;
use App\Domain\Reporting\Enums\ExportStatus;
use App\Domain\Reporting\Enums\MetricKey;
use App\Domain\Reporting\Enums\ReportFrequency;
use App\Domain\Reporting\Enums\ReportStatus;
use App\Domain\Reporting\Exceptions\DuplicateExportRequestException;
use App\Domain\Reporting\Exceptions\InvalidReportParameterException;
use App\Domain\Reporting\Models\AnalyticsEvent;
use App\Domain\Reporting\Services\AnalyticsCollector;
use App\Domain\Reporting\Services\ExportEngine;
use App\Domain\Reporting\Services\KPIEngine;
use App\Domain\Reporting\Services\ReportGenerator;
use App\Domain\Reporting\Services\ScheduledReportService;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportingPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_collection_stores_event_without_operational_querying(): void
    {
        $tenant = $this->tenant();

        $event = app(AnalyticsCollector::class)->collect(new AnalyticsEventData(
            eventName: 'payment_succeeded',
            sourceDomain: AnalyticsSourceDomain::PAYMENT,
            eventReference: 'payment#001',
            occurredAt: now(),
            tenantId: $tenant->id,
            measures: ['amount' => 1500, 'count' => 1],
        ));

        $this->assertSame('payment_succeeded', $event->event_name);
        $this->assertSame(1, AnalyticsEvent::query()->where('event_reference', 'payment#001')->count());
    }

    public function test_kpi_engine_calculates_total_revenue_from_analytics_events(): void
    {
        $tenant = $this->tenant();
        $this->collectPayment($tenant->id, 'payment#101', 1200);
        $this->collectPayment($tenant->id, 'payment#102', 800);

        $metric = app(KPIEngine::class)->calculate(new KPIQueryData(
            metricKey: MetricKey::TOTAL_REVENUE,
            from: now()->startOfDay(),
            to: now()->endOfDay(),
            tenantId: $tenant->id,
        ));

        $this->assertSame(MetricKey::TOTAL_REVENUE, $metric->metric_key);
        $this->assertSame('2000.000000', $metric->value);
    }

    public function test_report_generation_creates_reporting_snapshot(): void
    {
        $tenant = $this->tenant();
        $this->collectPayment($tenant->id, 'payment#201', 500);

        $snapshot = app(ReportGenerator::class)->generate(new ReportRequestData(
            reportKey: 'financial_summary',
            from: now()->startOfDay(),
            to: now()->endOfDay(),
            tenantId: $tenant->id,
        ));

        $this->assertSame('financial_summary', $snapshot->snapshot_key);
        $this->assertArrayHasKey('total_revenue', $snapshot->data['metrics']);
    }

    public function test_export_generation_creates_pending_export_job_only(): void
    {
        $tenant = $this->tenant();

        $export = app(ExportEngine::class)->request(new ExportRequestData(
            reportKey: 'financial_summary',
            format: ExportFormat::CSV,
            tenantId: $tenant->id,
            dedupeKey: 'export#001',
        ));

        $this->assertSame(ExportFormat::CSV, $export->format);
        $this->assertSame(ExportStatus::PENDING, $export->status);
        $this->assertNull($export->file_path);
    }

    public function test_scheduled_reporting_creates_daily_weekly_or_monthly_job(): void
    {
        $tenant = $this->tenant();

        $job = app(ScheduledReportService::class)->schedule(new ScheduledReportData(
            reportKey: 'payment_summary',
            frequency: ReportFrequency::WEEKLY,
            scheduledFor: now()->addWeek(),
            tenantId: $tenant->id,
        ));

        $this->assertSame(ReportFrequency::WEEKLY, $job->frequency);
        $this->assertSame(ReportStatus::PENDING, $job->status);
    }

    public function test_invalid_date_range_is_rejected(): void
    {
        $this->expectException(InvalidReportParameterException::class);

        app(ReportGenerator::class)->generate(new ReportRequestData(
            reportKey: 'financial_summary',
            from: now()->addDay(),
            to: now(),
        ));
    }

    public function test_duplicate_export_request_is_rejected(): void
    {
        $request = new ExportRequestData('financial_summary', ExportFormat::PDF, dedupeKey: 'export#duplicate');
        app(ExportEngine::class)->request($request);

        $this->expectException(DuplicateExportRequestException::class);
        app(ExportEngine::class)->request($request);
    }

    private function collectPayment(string $tenantId, string $reference, int $amount): void
    {
        app(AnalyticsCollector::class)->collect(new AnalyticsEventData(
            eventName: 'payment_succeeded',
            sourceDomain: AnalyticsSourceDomain::PAYMENT,
            eventReference: $reference,
            occurredAt: now(),
            tenantId: $tenantId,
            measures: ['amount' => $amount, 'count' => 1],
        ));
    }

    private function tenant(): Tenant
    {
        return Tenant::query()->create([
            'name' => 'Reporting Test Tenant',
            'slug' => 'reporting-test-'.Str::lower((string) Str::ulid()),
            'status' => 'active',
        ]);
    }
}
