<?php

namespace App\Domain\Ledger\ValueObjects;

use App\Domain\Ledger\Enums\LedgerEntryType;
use App\Domain\Wallet\ValueObjects\Money;

readonly class LedgerLine
{
    public function __construct(
        public string $accountId,
        public LedgerEntryType $type,
        public Money $amount,
        public array $metadata = [],
    ) {
    }
}
