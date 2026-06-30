<?php

namespace App\Domain\Verification\Interfaces;

use App\Contracts\Verification\VerificationProviderContract;

interface VerificationProviderManagerInterface
{
    public function driver(string $provider): VerificationProviderContract;
}
