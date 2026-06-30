<?php

namespace App\Domain\Wallet\Services;

use App\Domain\Wallet\Enums\ReservationStatus;
use App\Domain\Wallet\Enums\TransactionStatus;
use App\Domain\Wallet\Enums\TransactionType;
use App\Domain\Wallet\Events\WalletReleased;
use App\Domain\Wallet\Events\WalletReserved;
use App\Domain\Wallet\Interfaces\ReservationInterface;
use App\Domain\Wallet\Interfaces\WalletRepositoryInterface;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\Models\WalletReservation;
use App\Domain\Wallet\Models\WalletTransaction;
use App\Domain\Wallet\Services\Concerns\UpdatesWalletAccounts;
use App\Domain\Wallet\Validators\WalletValidationService;
use App\Domain\Wallet\ValueObjects\Money;
use App\Services\BaseService;
use Illuminate\Support\Str;

class WalletReservationService extends BaseService implements ReservationInterface
{
    use UpdatesWalletAccounts;

    public function __construct(
        private readonly WalletRepositoryInterface $wallets,
        private readonly WalletValidationService $validator,
    ) {
    }

    public function reserve(Wallet $wallet, Money $amount, string $reference, ?\DateTimeInterface $expiresAt = null, array $metadata = []): WalletReservation
    {
        return $this->transaction(function () use ($wallet, $amount, $reference, $expiresAt, $metadata): WalletReservation {
            $lockedWallet = $this->wallets->findLocked($wallet->id);

            $this->validator->assertWalletCanMutate($lockedWallet);
            $this->validator->assertPositive($amount);
            $this->validator->assertCurrencyMatches($lockedWallet, $amount);
            $this->validator->assertSufficientAvailableBalance($lockedWallet, $amount);
            $this->validator->assertReferenceIsUnique($reference);

            $availableBefore = Money::fromDecimal($lockedWallet->available_balance, $lockedWallet->currency);
            $reservedBefore = Money::fromDecimal($lockedWallet->reserved_balance, $lockedWallet->currency);
            $availableAfter = $availableBefore->subtract($amount);
            $reservedAfter = $reservedBefore->add($amount);

            $lockedWallet->forceFill([
                'available_balance' => $availableAfter->decimal(),
                'reserved_balance' => $reservedAfter->decimal(),
            ])->save();

            $this->syncAccount($lockedWallet, 'available', $availableAfter->decimal());
            $this->syncAccount($lockedWallet, 'reserved', $reservedAfter->decimal());

            $transaction = WalletTransaction::query()->create([
                'tenant_id' => $lockedWallet->tenant_id,
                'wallet_id' => $lockedWallet->id,
                'reference' => $reference,
                'type' => TransactionType::RESERVATION,
                'status' => TransactionStatus::SUCCESS,
                'amount' => $amount->decimal(),
                'currency' => $amount->currency->value(),
                'balance_before' => $availableBefore->decimal(),
                'balance_after' => $availableAfter->decimal(),
                'metadata' => $metadata,
            ]);

            $reservation = WalletReservation::query()->create([
                'tenant_id' => $lockedWallet->tenant_id,
                'wallet_id' => $lockedWallet->id,
                'wallet_transaction_id' => $transaction->id,
                'reference' => $reference,
                'amount' => $amount->decimal(),
                'currency' => $amount->currency->value(),
                'status' => ReservationStatus::ACTIVE,
                'expires_at' => $expiresAt,
                'metadata' => $metadata,
            ]);

            WalletReserved::dispatch($reservation);

            return $reservation;
        });
    }

    public function release(WalletReservation $reservation, ?Money $amount = null): WalletReservation
    {
        return $this->transaction(function () use ($reservation, $amount): WalletReservation {
            $lockedWallet = $this->wallets->findLocked($reservation->wallet_id);
            $reservation = WalletReservation::query()->whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            $this->validator->assertReservationCanRelease($reservation);

            $releaseAmount = $amount ?? Money::fromDecimal($reservation->amount, $reservation->currency);
            $this->validator->assertPositive($releaseAmount);
            $reservedBefore = Money::fromDecimal($lockedWallet->reserved_balance, $lockedWallet->currency);
            $availableBefore = Money::fromDecimal($lockedWallet->available_balance, $lockedWallet->currency);
            $releasedBefore = Money::fromDecimal($reservation->released_amount, $reservation->currency);
            $reservedOriginal = Money::fromDecimal($reservation->amount, $reservation->currency);
            $remainingReservation = $reservedOriginal->subtract($releasedBefore);

            if ($releaseAmount->greaterThan($reservedBefore)) {
                throw new \App\Domain\Wallet\Exceptions\InvalidWalletOperationException('Release amount exceeds reserved balance.');
            }

            if ($releaseAmount->greaterThan($remainingReservation)) {
                throw new \App\Domain\Wallet\Exceptions\InvalidWalletOperationException('Release amount exceeds reservation remaining balance.');
            }

            $reservedAfter = $reservedBefore->subtract($releaseAmount);
            $availableAfter = $availableBefore->add($releaseAmount);

            $lockedWallet->forceFill([
                'available_balance' => $availableAfter->decimal(),
                'reserved_balance' => $reservedAfter->decimal(),
            ])->save();

            $releasedAfter = $releasedBefore->add($releaseAmount);

            $reservation->forceFill([
                'released_amount' => $releasedAfter->decimal(),
                'status' => $releasedAfter->minorUnits >= $reservedOriginal->minorUnits ? ReservationStatus::RELEASED : ReservationStatus::ACTIVE,
            ])->save();

            $this->syncAccount($lockedWallet, 'available', $availableAfter->decimal());
            $this->syncAccount($lockedWallet, 'reserved', $reservedAfter->decimal());

            WalletTransaction::query()->create([
                'tenant_id' => $lockedWallet->tenant_id,
                'wallet_id' => $lockedWallet->id,
                'reference' => $reservation->reference.'-REL-'.strtoupper((string) Str::ulid()),
                'type' => TransactionType::RELEASE,
                'status' => TransactionStatus::SUCCESS,
                'amount' => $releaseAmount->decimal(),
                'currency' => $releaseAmount->currency->value(),
                'balance_before' => $availableBefore->decimal(),
                'balance_after' => $availableAfter->decimal(),
                'related_type' => $reservation::class,
                'related_id' => $reservation->id,
            ]);

            WalletReleased::dispatch($reservation);

            return $reservation;
        });
    }
}
