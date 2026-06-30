<?php

namespace App\Domain\Reporting\Services;

use App\Domain\Reporting\DTOs\KPIQueryData;
use App\Domain\Reporting\Enums\MetricCategory;
use App\Domain\Reporting\Enums\MetricKey;
use App\Domain\Reporting\Events\KPIUpdated;
use App\Domain\Reporting\Interfaces\KPIEngineInterface;
use App\Domain\Reporting\Interfaces\ReportingRepositoryInterface;
use App\Domain\Reporting\Models\ReportingMetric;
use App\Domain\Reporting\Validators\ReportingValidationService;

class KPIEngine implements KPIEngineInterface
{
    public function __construct(
        private readonly ReportingRepositoryInterface $reports,
        private readonly ReportingValidationService $validator,
    ) {
    }

    public function calculate(KPIQueryData $query): ReportingMetric
    {
        $this->validator->validateKpiQuery($query);

        [$value, $category, $unit] = match ($query->metricKey) {
            MetricKey::TOTAL_REVENUE, MetricKey::DAILY_REVENUE, MetricKey::MONTHLY_REVENUE, MetricKey::PRODUCT_REVENUE, MetricKey::TENANT_REVENUE
                => [$this->reports->sumEvents($query, 'amount', 'payment_succeeded'), MetricCategory::FINANCIAL, 'currency'],
            MetricKey::WALLET_CREDITS => [$this->reports->sumEvents($query, 'amount', 'wallet_credited'), MetricCategory::FINANCIAL, 'currency'],
            MetricKey::WALLET_DEBITS => [$this->reports->sumEvents($query, 'amount', 'wallet_debited'), MetricCategory::FINANCIAL, 'currency'],
            MetricKey::WALLET_BALANCE => [$this->reports->sumEvents($query, 'amount', 'wallet_credited') - $this->reports->sumEvents($query, 'amount', 'wallet_debited'), MetricCategory::FINANCIAL, 'currency'],
            MetricKey::SUCCESSFUL_VERIFICATIONS => [$this->reports->countEvents($query, 'verification_completed'), MetricCategory::VERIFICATION, 'count'],
            MetricKey::FAILED_VERIFICATIONS => [$this->reports->countEvents($query, 'verification_failed'), MetricCategory::VERIFICATION, 'count'],
            MetricKey::PROVIDER_SUCCESS_RATE => [$this->rate($this->reports->countEvents($query, 'verification_completed'), $this->reports->countEvents($query, 'verification_failed')), MetricCategory::VERIFICATION, 'percent'],
            MetricKey::AVERAGE_PROCESSING_TIME => [$this->averageDuration($query), MetricCategory::VERIFICATION, 'milliseconds'],
            MetricKey::PAYMENT_SUCCESS_RATE => [$this->rate($this->reports->countEvents($query, 'payment_succeeded'), $this->reports->countEvents($query, 'payment_failed')), MetricCategory::PAYMENT, 'percent'],
            MetricKey::FAILED_PAYMENTS => [$this->reports->countEvents($query, 'payment_failed'), MetricCategory::PAYMENT, 'count'],
            MetricKey::PENDING_PAYMENTS => [$this->reports->countEvents($query, 'payment_initialized'), MetricCategory::PAYMENT, 'count'],
            MetricKey::REFUND_VOLUME => [$this->reports->sumEvents($query, 'amount', 'refund_issued'), MetricCategory::PAYMENT, 'currency'],
            MetricKey::TOP_SELLING_PRODUCTS, MetricKey::PRODUCT_USAGE => [$this->reports->countEvents($query, 'product_created'), MetricCategory::PRODUCT, 'count'],
            MetricKey::ACTIVE_TENANTS, MetricKey::TENANT_GROWTH => [$this->reports->countEvents($query, 'tenant_created'), MetricCategory::TENANT, 'count'],
            MetricKey::NOTIFICATION_VOLUME => [$this->reports->countEvents($query, 'notification_sent'), MetricCategory::SYSTEM, 'count'],
            MetricKey::QUEUE_STATISTICS, MetricKey::API_USAGE => [$this->reports->countEvents($query), MetricCategory::SYSTEM, 'count'],
        };

        $metric = $this->reports->upsertMetric([
            'tenant_id' => $query->tenantId,
            'metric_key' => $query->metricKey,
            'category' => $category,
            'value' => $value,
            'unit' => $unit,
            'period_date' => $query->from->toDateString(),
            'period_type' => $query->periodType,
            'dimensions' => ['from' => $query->from->toDateString(), 'to' => $query->to->toDateString()],
            'calculated_at' => now(),
        ]);

        event(new KPIUpdated($metric));

        return $metric;
    }

    private function rate(int $successes, int $failures): float
    {
        $total = $successes + $failures;

        return $total === 0 ? 0.0 : round(($successes / $total) * 100, 2);
    }

    private function averageDuration(KPIQueryData $query): float
    {
        $duration = (float) $this->reports->sumEvents($query, 'duration_ms');
        $count = $this->reports->countEvents($query);

        return $count === 0 ? 0.0 : round($duration / $count, 2);
    }
}
