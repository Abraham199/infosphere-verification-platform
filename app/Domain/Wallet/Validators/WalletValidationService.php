<?php

namespace App\Domain\Wallet\Validators;

use App\Domain\Wallet\Enums\ReservationStatus;
use App\Domain\Wallet\Enums\WalletStatus;
use App\Domain\Wallet\Exceptions\DuplicateTransactionReferenceException;
use App\Domain\Wallet\Exceptions\InsufficientFundsException;
use App\Domain\Wallet\Exceptions\InvalidWalletOperationException;
use App\Domain\Wallet\Interfaces\WalletRepositoryInterface;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\Models\WalletReservation;
use App\Domain\Wallet\ValueObjects\Money;

class WalletValidationService
{
    public function __construct(private readonly WalletRepositoryInterface $wallets)
    {
    }

    public function assertWalletCanMutate(Wallet $wallet): void
    {
        if ($wallet->status !== WalletStatus::ACTIVE) {
            throw new InvalidWalletOperationException('Wallet is not active.');
        }
    }

    public function assertPositive(Money $amount): void
    {
        if ($amount->isZero()) {
            throw new InvalidWalletOperationException('Amount must be greater than zero.');
        }
    }

    public function assertSufficientAvailableBalance(Wallet $wallet, Money $amount): void
    {
        $available = Money::fromDecimal($wallet->available_balance, $wallet->currency);

        if ($amount->greaterThan($available)) {
            throw new InsufficientFundsException('Wallet has insufficient available balance.');
        }
    }

    public function assertReferenceIsUnique(string $reference): void
    {
        if ($this->wallets->referenceExists($reference)) {
            throw new DuplicateTransactionReferenceException('Wallet transaction reference already exists.');
        }
    }

    public function assertCurrencyMatches(Wallet $wallet, Money $amount): void
    {
        if ($wallet->currency !== $amount->currency->value()) {
            throw new InvalidWalletOperationException('Wallet currency does not match transaction currency.');
        }
    }

    public function assertReservationCanRelease(WalletReservation $reservation): void
    {
        if ($reservation->status !== ReservationStatus::ACTIVE) {
            throw new InvalidWalletOperationException('Reservation is not active.');
        }
    }

    public function assertRefundIsValid(Wallet $wallet, Money $amount): void
    {
        $this->assertWalletCanMutate($wallet);
        $this->assertPositive($amount);
        $this->assertCurrencyMatches($wallet, $amount);
    }
}
