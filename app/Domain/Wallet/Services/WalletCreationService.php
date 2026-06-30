<?php

namespace App\Domain\Wallet\Services;

use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Wallet\Enums\WalletStatus;
use App\Domain\Wallet\Events\WalletCreated;
use App\Domain\Wallet\Models\Wallet;
use App\Services\BaseService;

class WalletCreationService extends BaseService
{
    public function createForTenant(Tenant $tenant, string $currency = 'NGN'): Wallet
    {
        return $this->transaction(function () use ($tenant, $currency): Wallet {
            $wallet = Wallet::query()->firstOrCreate([
                'tenant_id' => $tenant->id,
                'currency' => strtoupper($currency),
            ], [
                'status' => WalletStatus::ACTIVE,
            ]);

            foreach (['available', 'pending', 'frozen', 'reserved', 'refund'] as $accountType) {
                $wallet->accounts()->firstOrCreate([
                    'account_type' => $accountType,
                ], [
                    'tenant_id' => $tenant->id,
                    'currency' => $wallet->currency,
                    'balance' => 0,
                    'status' => 'active',
                ]);
            }

            if ($wallet->wasRecentlyCreated) {
                WalletCreated::dispatch($wallet);
            }

            return $wallet->refresh();
        });
    }
}
