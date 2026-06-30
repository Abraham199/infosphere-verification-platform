<?php

namespace App\Domain\Developer\Events;

use App\Domain\Developer\Models\DeveloperApplication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SandboxApplicationCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly DeveloperApplication $application)
    {
    }
}
