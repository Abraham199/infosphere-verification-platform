<?php

namespace App\Domain\Verification\Interfaces;

interface VerificationResponseInterface
{
    public function successful(): bool;

    public function providerReference(): ?string;

    public function resultStatus(): ?string;
}
