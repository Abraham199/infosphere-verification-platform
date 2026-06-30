<?php

namespace App\Domain\Reporting\Interfaces;

use App\Domain\Reporting\DTOs\AnalyticsEventData;
use App\Domain\Reporting\Models\AnalyticsEvent;

interface AnalyticsCollectorInterface
{
    public function collect(AnalyticsEventData $event): AnalyticsEvent;
    public function collectFromDomainEvent(object $event): ?AnalyticsEvent;
}
