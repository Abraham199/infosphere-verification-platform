<?php

namespace App\Domain\Ledger\ValueObjects;

use App\Domain\Wallet\ValueObjects\Currency;
use InvalidArgumentException;

readonly class JournalEntry
{
    /**
     * @param array<int, LedgerLine> $lines
     */
    public function __construct(
        public ?string $tenantId,
        public string $reference,
        public string $description,
        public Currency $currency,
        public array $lines,
        public array $metadata = [],
        public ?string $sourceType = null,
        public ?string $sourceId = null,
    ) {
        if (count($lines) < 2) {
            throw new InvalidArgumentException('Journal entry must contain at least two lines.');
        }
    }
}
