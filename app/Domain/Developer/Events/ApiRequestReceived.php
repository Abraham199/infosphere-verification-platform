<?php

namespace App\Domain\Developer\Events;

use App\Domain\Developer\DTOs\ApiRequestContext;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApiRequestReceived
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ApiRequestContext $context)
    {
    }
}
