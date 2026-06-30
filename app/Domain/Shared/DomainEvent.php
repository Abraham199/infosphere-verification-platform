<?php

namespace App\Domain\Shared;

abstract class DomainEvent
{
    public function occurredAt(): string
    {
        return now()->toIso8601String();
    }
}
