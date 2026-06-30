<?php

namespace App\Domain\Wallet\ValueObjects;

use InvalidArgumentException;

readonly class Money
{
    public function __construct(public int $minorUnits, public Currency $currency)
    {
        if ($minorUnits < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }
    }

    public static function fromDecimal(string|int|float $amount, string|Currency $currency): self
    {
        $currency = is_string($currency) ? new Currency($currency) : $currency;
        $normalized = number_format((float) $amount, 2, '.', '');

        return new self((int) round(((float) $normalized) * 100), $currency);
    }

    public function decimal(): string
    {
        return number_format($this->minorUnits / 100, 2, '.', '');
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->minorUnits + $other->minorUnits, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        if ($other->minorUnits > $this->minorUnits) {
            throw new InvalidArgumentException('Money subtraction cannot produce a negative amount.');
        }

        return new self($this->minorUnits - $other->minorUnits, $this->currency);
    }

    public function greaterThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->minorUnits > $other->minorUnits;
    }

    public function isZero(): bool
    {
        return $this->minorUnits === 0;
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency->value() !== $other->currency->value()) {
            throw new InvalidArgumentException('Currency mismatch.');
        }
    }
}
