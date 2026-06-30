<?php

namespace App\Domain\Wallet\Services;

use App\Domain\Wallet\Interfaces\BalanceCalculatorInterface;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\ValueObjects\Balance;
use App\Domain\Wallet\ValueObjects\Money;

class WalletBalanceService implements BalanceCalculatorInterface
{
    public function calculate(Wallet $wallet): Balance
    {
        return new Balance(
            available: Money::fromDecimal($wallet->available_balance, $wallet->currency),
            pending: Money::fromDecimal($wallet->pending_balance, $wallet->currency),
            frozen: Money::fromDecimal($wallet->frozen_balance, $wallet->currency),
            reserved: Money::fromDecimal($wallet->reserved_balance, $wallet->currency),
            refund: Money::fromDecimal($wallet->refund_balance, $wallet->currency),
        );
    }
}
