<?php

namespace App\Listeners;

use App\Domain\Reporting\Interfaces\AnalyticsCollectorInterface;

class CollectAnalyticsEvent
{
    public function __construct(private readonly AnalyticsCollectorInterface $collector)
    {
    }

    public function handle(object $event): void
    {
        $this->collector->collectFromDomainEvent($event);
    }
}
