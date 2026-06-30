<?php

namespace App\Domain\Verification\Services;

use App\Domain\Verification\Enums\VerificationServiceStatus;
use App\Domain\Verification\Models\VerificationPricingRule;
use App\Domain\Verification\Models\VerificationService;
use App\Domain\Wallet\ValueObjects\Money;

class VerificationPricingService
{
    public function resolve(string $tenantId, VerificationService $service): Money
    {
        $tenantRule = VerificationPricingRule::query()
            ->where('tenant_id', $tenantId)
            ->where('verification_service_id', $service->id)
            ->where('status', VerificationServiceStatus::ACTIVE)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->latest()
            ->first();

        if ($tenantRule) {
            return Money::fromDecimal($tenantRule->price, $tenantRule->currency);
        }

        if ($service->global_price !== null) {
            return Money::fromDecimal($service->global_price, $service->currency);
        }

        return Money::fromDecimal($service->default_price, $service->currency);
    }
}
