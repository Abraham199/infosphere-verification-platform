<?php

namespace App\Domain\Wallet\Services;

use App\Domain\Wallet\Enums\TransactionStatus;
use App\Domain\Wallet\Enums\TransactionType;
use App\Domain\Wallet\Events\WalletAdjusted;
use App\Domain\Wallet\Interfaces\WalletRepositoryInterface;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\Models\WalletAdjustment;
use App\Domain\Wallet\Models\WalletTransaction;
use App\Domain\Wallet\Services\Concerns\UpdatesWalletAccounts;
use App\Domain\Wallet\Validators\WalletValidationService;
use App\Domain\Wallet\ValueObjects\Money;
use App\Services\BaseService;

class WalletAdjustmentService extends BaseService
{
    use UpdatesWalletAccounts;

    public function __construct(
        private readonly WalletRepositoryInterface $wallets,
        private readonly WalletValidationService $validator,
    ) {
    }

    public function apply(Wallet $wallet, Money $amount, string $direction, string $reference, string $reason, ?string $approvedBy = null, array $metadata = []): WalletAdjustment
    {
        return $this->transaction(function () use ($wallet, $amount, $direction, $reference, $reason, $approvedBy, $metadata): WalletAdjustment {
            $lockedWallet = $this->wallets->findLocked($wallet->id);

            $this->validator->assertWalletCanMutate($lockedWallet);
            $this->validator->assertPositive($amount);
            $this->validator->assertCurrencyMatches($lockedWallet, $amount);
            $this->validator->assertReferenceIsUnique($reference);

            if ($direction === 'debit') {
                $this->validator->assertSufficientAvailableBalance($lockedWallet, $amount);
            }

            $before = Money::fromDecimal($lockedWallet->available_balance, $lockedWallet->currency);
            $after = match ($direction) {
                'credit' => $before->add($amount),
                'debit' => $before->subtract($amount),
                default => throw new \App\Domain\Wallet\Exceptions\InvalidWalletOperationException('Adjustment direction must be credit or debit.'),
            };

            $lockedWallet->forceFill([
                'available_balance' => $after->decimal(),
            ])->save();

            $this->syncAccount($lockedWallet, 'available', $after->decimal());

            $transaction = WalletTransaction::query()->create([
                'tenant_id' => $lockedWallet->tenant_id,
                'wallet_id' => $lockedWallet->id,
                'reference' => $reference,
                'type' => TransactionType::ADJUSTMENT,
                'status' => TransactionStatus::SUCCESS,
                'amount' => $amount->decimal(),
                'currency' => $amount->currency->value(),
                'balance_before' => $before->decimal(),
                'balance_after' => $after->decimal(),
                'metadata' => $metadata,
            ]);

            $adjustment = WalletAdjustment::query()->create([
                'tenant_id' => $lockedWallet->tenant_id,
                'wallet_id' => $lockedWallet->id,
                'wallet_transaction_id' => $transaction->id,
                'reference' => $reference,
                'direction' => $direction,
                'amount' => $amount->decimal(),
                'currency' => $amount->currency->value(),
                'reason' => $reason,
                'approved_by' => $approvedBy,
                'approved_at' => now(),
                'status' => TransactionStatus::SUCCESS,
                'metadata' => $metadata,
            ]);

            WalletAdjusted::dispatch($adjustment);

            return $adjustment;
        });
    }
}
