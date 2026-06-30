<?php

namespace App\Domain\Wallet\ValueObjects;

use InvalidArgumentException;

readonly class Currency
{
    public function __construct(public string $code)
    {
        $normalized = strtoupper($this->code);

        if (! preg_match('/^[A-Z]{3}$/', $normalized)) {
            throw new InvalidArgumentException('Currency must be a valid ISO-style 3-letter code.');
        }
    }

    public function value(): string
    {
        return strtoupper($this->code);
    }
}
