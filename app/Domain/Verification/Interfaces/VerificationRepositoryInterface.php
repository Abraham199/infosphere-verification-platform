<?php

namespace App\Domain\Verification\Interfaces;

use App\Domain\Verification\Models\VerificationRequest;
use App\Domain\Verification\Models\VerificationService;

interface VerificationRepositoryInterface
{
    public function findServiceByCode(string $serviceCode): ?VerificationService;

    public function referenceExists(string $reference): bool;

    public function findByIdempotencyKey(?string $idempotencyKey): ?VerificationRequest;

    public function findLocked(string $requestId): VerificationRequest;
}
