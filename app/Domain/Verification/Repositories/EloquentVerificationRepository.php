<?php

namespace App\Domain\Verification\Repositories;

use App\Domain\Verification\Interfaces\VerificationRepositoryInterface;
use App\Domain\Verification\Models\VerificationRequest;
use App\Domain\Verification\Models\VerificationService;

class EloquentVerificationRepository implements VerificationRepositoryInterface
{
    public function findServiceByCode(string $serviceCode): ?VerificationService
    {
        return VerificationService::query()->where('service_code', $serviceCode)->first();
    }

    public function referenceExists(string $reference): bool
    {
        return VerificationRequest::query()->where('reference', $reference)->exists();
    }

    public function findByIdempotencyKey(?string $idempotencyKey): ?VerificationRequest
    {
        return $idempotencyKey ? VerificationRequest::query()->where('idempotency_key', $idempotencyKey)->first() : null;
    }

    public function findLocked(string $requestId): VerificationRequest
    {
        return VerificationRequest::query()->whereKey($requestId)->lockForUpdate()->firstOrFail();
    }
}
