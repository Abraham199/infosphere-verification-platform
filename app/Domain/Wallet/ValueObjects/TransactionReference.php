<?php

namespace App\Domain\Wallet\ValueObjects;

use Illuminate\Support\Str;
use InvalidArgumentException;

readonly class TransactionReference
{
    public function __construct(public string $value)
    {
        if (! preg_match('/^[A-Z0-9_\-]{12,80}$/', $value)) {
            throw new InvalidArgumentException('Transaction reference format is invalid.');
        }
    }

    public static function generate(string $prefix = 'WALLET'): self
    {
        return new self(strtoupper($prefix).'-'.strtoupper((string) Str::ulid()));
    }
}
