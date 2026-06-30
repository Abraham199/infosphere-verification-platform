<?php

namespace App\Domain\Reporting\Services;

use App\Domain\Reporting\DTOs\KPIQueryData;
use App\Domain\Reporting\DTOs\ReportRequestData;
use App\Domain\Reporting\Enums\MetricKey;
use App\Domain\Reporting\Events\ReportGenerated;
use App\Domain\Reporting\Interfaces\KPIEngineInterface;
use App\Domain\Reporting\Interfaces\ReportGeneratorInterface;
use App\Domain\Reporting\Interfaces\ReportingRepositoryInterface;
use App\Domain\Reporting\Models\ReportingSnapshot;
use App\Domain\Reporting\Validators\ReportingValidationService;

class ReportGenerator implements ReportGeneratorInterface
{
    public function __construct(
        private readonly ReportingRepositoryInterface $reports,
        private readonly KPIEngineInterface $kpis,
        private readonly ReportingValidationService $validator,
    ) {
    }

    public function generate(ReportRequestData $request): ReportingSnapshot
    {
        $this->validator->validateReportRequest($request);

        $metricKeys = $this->metricKeysFor($request->reportKey);
        $metrics = [];

        foreach ($metricKeys as $metricKey) {
            $metric = $this->kpis->calculate(new KPIQueryData(
                metricKey: $metricKey,
                from: $request->from,
                to: $request->to,
                tenantId: $request->tenantId,
            ));

            $metrics[$metricKey->value] = [
                'value' => $metric->value,
                'unit' => $metric->unit,
            ];
        }

        $snapshot = $this->reports->createSnapshot([
            'tenant_id' => $request->tenantId,
            'snapshot_key' => $request->reportKey,
            'period_type' => $request->parameters['period_type'] ?? 'custom',
            'period_start' => $request->from->toDateString(),
            'period_end' => $request->to->toDateString(),
            'data' => ['metrics' => $metrics, 'parameters' => $request->parameters],
            'generated_at' => now(),
        ]);

        event(new ReportGenerated($snapshot));

        return $snapshot;
    }

    /**
     * @return array<int, MetricKey>
     */
    private function metricKeysFor(string $reportKey): array
    {
        return match ($reportKey) {
            'financial_summary' => [MetricKey::TOTAL_REVENUE, MetricKey::WALLET_CREDITS, MetricKey::WALLET_DEBITS],
            'verification_summary' => [MetricKey::SUCCESSFUL_VERIFICATIONS, MetricKey::FAILED_VERIFICATIONS, MetricKey::PROVIDER_SUCCESS_RATE],
            'payment_summary' => [MetricKey::PAYMENT_SUCCESS_RATE, MetricKey::FAILED_PAYMENTS, MetricKey::PENDING_PAYMENTS, MetricKey::REFUND_VOLUME],
            'product_summary' => [MetricKey::PRODUCT_REVENUE, MetricKey::PRODUCT_USAGE, MetricKey::TOP_SELLING_PRODUCTS],
            'tenant_summary' => [MetricKey::ACTIVE_TENANTS, MetricKey::TENANT_REVENUE, MetricKey::TENANT_GROWTH],
            'system_summary' => [MetricKey::NOTIFICATION_VOLUME, MetricKey::QUEUE_STATISTICS, MetricKey::API_USAGE],
            default => [MetricKey::TOTAL_REVENUE, MetricKey::SUCCESSFUL_VERIFICATIONS, MetricKey::PAYMENT_SUCCESS_RATE],
        };
    }
}
