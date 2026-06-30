<?php

namespace App\Domain\Wallet\ValueObjects;

readonly class Balance
{
    public function __construct(
        public Money $available,
        public Money $pending,
        public Money $frozen,
        public Money $reserved,
        public Money $refund,
    ) {
    }
}
