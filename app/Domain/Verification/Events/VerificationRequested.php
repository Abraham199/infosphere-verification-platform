<?php

namespace App\Domain\Verification\Events;

use App\Domain\Verification\Models\VerificationRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VerificationRequested
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly VerificationRequest $request)
    {
    }
}
