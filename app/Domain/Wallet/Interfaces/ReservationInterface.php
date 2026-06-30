<?php

namespace App\Domain\Wallet\Interfaces;

use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\Models\WalletReservation;
use App\Domain\Wallet\ValueObjects\Money;

interface ReservationInterface
{
    public function reserve(Wallet $wallet, Money $amount, string $reference, ?\DateTimeInterface $expiresAt = null, array $metadata = []): WalletReservation;

    public function release(WalletReservation $reservation, ?Money $amount = null): WalletReservation;
}
