<?php

namespace App\Domain\Wallet\Services;

use App\Domain\Wallet\Enums\WalletStatus;
use App\Domain\Wallet\Events\WalletFrozen;
use App\Domain\Wallet\Events\WalletUnfrozen;
use App\Domain\Wallet\Interfaces\WalletRepositoryInterface;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\Services\Concerns\UpdatesWalletAccounts;
use App\Domain\Wallet\Validators\WalletValidationService;
use App\Domain\Wallet\ValueObjects\Money;
use App\Services\BaseService;

class WalletFreezeService extends BaseService
{
    use UpdatesWalletAccounts;

    public function __construct(
        private readonly WalletRepositoryInterface $wallets,
        private readonly WalletValidationService $validator,
    ) {
    }

    public function freeze(Wallet $wallet, Money $amount, string $reason): Wallet
    {
        return $this->transaction(function () use ($wallet, $amount, $reason): Wallet {
            $lockedWallet = $this->wallets->findLocked($wallet->id);

            $this->validator->assertWalletCanMutate($lockedWallet);
            $this->validator->assertPositive($amount);
            $this->validator->assertCurrencyMatches($lockedWallet, $amount);
            $this->validator->assertSufficientAvailableBalance($lockedWallet, $amount);

            $availableAfter = Money::fromDecimal($lockedWallet->available_balance, $lockedWallet->currency)->subtract($amount);
            $frozenAfter = Money::fromDecimal($lockedWallet->frozen_balance, $lockedWallet->currency)->add($amount);

            $lockedWallet->forceFill([
                'available_balance' => $availableAfter->decimal(),
                'frozen_balance' => $frozenAfter->decimal(),
                'status' => WalletStatus::FROZEN,
                'locked_at' => now(),
                'locked_reason' => $reason,
            ])->save();

            $this->syncAccount($lockedWallet, 'available', $availableAfter->decimal());
            $this->syncAccount($lockedWallet, 'frozen', $frozenAfter->decimal());

            WalletFrozen::dispatch($lockedWallet);

            return $lockedWallet->refresh();
        });
    }

    public function unfreeze(Wallet $wallet, ?Money $amount = null): Wallet
    {
        return $this->transaction(function () use ($wallet, $amount): Wallet {
            $lockedWallet = $this->wallets->findLocked($wallet->id);
            $releaseAmount = $amount ?? Money::fromDecimal($lockedWallet->frozen_balance, $lockedWallet->currency);

            if (in_array($lockedWallet->status, [WalletStatus::SUSPENDED, WalletStatus::CLOSED], true)) {
                throw new \App\Domain\Wallet\Exceptions\InvalidWalletOperationException('Suspended or closed wallets cannot be unfrozen.');
            }

            $this->validator->assertPositive($releaseAmount);
            $this->validator->assertCurrencyMatches($lockedWallet, $releaseAmount);

            $frozenBefore = Money::fromDecimal($lockedWallet->frozen_balance, $lockedWallet->currency);

            if ($releaseAmount->greaterThan($frozenBefore)) {
                throw new \App\Domain\Wallet\Exceptions\InvalidWalletOperationException('Unfreeze amount exceeds frozen balance.');
            }

            $availableAfter = Money::fromDecimal($lockedWallet->available_balance, $lockedWallet->currency)->add($releaseAmount);
            $frozenAfter = $frozenBefore->subtract($releaseAmount);

            $lockedWallet->forceFill([
                'available_balance' => $availableAfter->decimal(),
                'frozen_balance' => $frozenAfter->decimal(),
                'status' => $frozenAfter->isZero() ? WalletStatus::ACTIVE : WalletStatus::FROZEN,
                'locked_at' => $frozenAfter->isZero() ? null : $lockedWallet->locked_at,
                'locked_reason' => $frozenAfter->isZero() ? null : $lockedWallet->locked_reason,
            ])->save();

            $this->syncAccount($lockedWallet, 'available', $availableAfter->decimal());
            $this->syncAccount($lockedWallet, 'frozen', $frozenAfter->decimal());

            WalletUnfrozen::dispatch($lockedWallet);

            return $lockedWallet->refresh();
        });
    }
}
