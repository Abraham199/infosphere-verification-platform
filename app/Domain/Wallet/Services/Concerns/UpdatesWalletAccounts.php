<?php

namespace App\Domain\Wallet\Services\Concerns;

use App\Domain\Wallet\Models\Wallet;

trait UpdatesWalletAccounts
{
    private function syncAccount(Wallet $wallet, string $accountType, string $balance): void
    {
        $wallet->accounts()->updateOrCreate([
            'account_type' => $accountType,
        ], [
            'tenant_id' => $wallet->tenant_id,
            'currency' => $wallet->currency,
            'balance' => $balance,
            'status' => 'active',
        ]);
    }
}
