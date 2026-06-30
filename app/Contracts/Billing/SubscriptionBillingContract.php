<?php

namespace App\Contracts\Billing;

interface SubscriptionBillingContract
{
    public function currentPlan(string $tenantId): ?array;

    public function canUseFeature(string $tenantId, string $feature): bool;
}
