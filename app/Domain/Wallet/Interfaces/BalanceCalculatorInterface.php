<?php

namespace App\Domain\Wallet\Interfaces;

use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\ValueObjects\Balance;

interface BalanceCalculatorInterface
{
    public function calculate(Wallet $wallet): Balance;
}
