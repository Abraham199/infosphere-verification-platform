<?php

namespace App\Domain\Developer\Events;

use App\Domain\Developer\Models\ApiKey;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApiKeyRevoked
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ApiKey $apiKey)
    {
    }
}
