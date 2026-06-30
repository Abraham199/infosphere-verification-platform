<?php

namespace App\Domain\Shared;

abstract readonly class ValueObject
{
    abstract public function equals(self $other): bool;
}
