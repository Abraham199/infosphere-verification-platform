<?php

namespace App\Domain\Reporting\Events;

use App\Domain\Reporting\Models\AnalyticsEvent;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnalyticsCollected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly AnalyticsEvent $event)
    {
    }
}
