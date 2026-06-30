<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VerificationCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly string $tenantId, public readonly string $reference)
    {
    }
}
