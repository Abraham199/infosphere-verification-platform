<?php

namespace App\Domain\Notification\Events;

use App\Domain\Notification\Models\NotificationPreference;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationPreferenceUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly NotificationPreference $preference)
    {
    }
}
