<?php

namespace App\Domain\Verification\Interfaces;

use App\Domain\Verification\DTOs\VerificationRequestData;
use App\Domain\Verification\Models\VerificationRequest;

interface VerificationServiceInterface
{
    public function request(VerificationRequestData $data): VerificationRequest;
}
