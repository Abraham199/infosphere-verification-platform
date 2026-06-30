<?php

namespace App\Domain\Wallet\Services;

use App\Domain\Wallet\Enums\TransactionStatus;
use App\Domain\Wallet\Enums\TransactionType;
use App\Domain\Wallet\Events\WalletDebited;
use App\Domain\Wallet\Interfaces\WalletRepositoryInterface;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\Models\WalletTransaction;
use App\Domain\Wallet\Services\Concerns\UpdatesWalletAccounts;
use App\Domain\Wallet\Validators\WalletValidationService;
use App\Domain\Wallet\ValueObjects\Money;
use App\Services\BaseService;

class WalletDebitService extends BaseService
{
    use UpdatesWalletAccounts;

    public function __construct(
        private readonly WalletRepositoryInterface $wallets,
        private readonly WalletValidationService $validator,
    ) {
    }

    public function debit(Wallet $wallet, Money $amount, string $reference, ?string $idempotencyKey = null, array $metadata = []): WalletTransaction
    {
        if ($existing = $this->wallets->idempotencyKeyExists($idempotencyKey)) {
            return $existing;
        }

        return $this->transaction(function () use ($wallet, $amount, $reference, $idempotencyKey, $metadata): WalletTransaction {
            $lockedWallet = $this->wallets->findLocked($wallet->id);

            $this->validator->assertWalletCanMutate($lockedWallet);
            $this->validator->assertPositive($amount);
            $this->validator->assertCurrencyMatches($lockedWallet, $amount);
            $this->validator->assertSufficientAvailableBalance($lockedWallet, $amount);
            $this->validator->assertReferenceIsUnique($reference);

            $before = Money::fromDecimal($lockedWallet->available_balance, $lockedWallet->currency);
            $after = $before->subtract($amount);

            $lockedWallet->forceFill([
                'available_balance' => $after->decimal(),
            ])->save();

            $this->syncAccount($lockedWallet, 'available', $after->decimal());

            $transaction = WalletTransaction::query()->create([
                'tenant_id' => $lockedWallet->tenant_id,
                'wallet_id' => $lockedWallet->id,
                'reference' => $reference,
                'idempotency_key' => $idempotencyKey,
                'type' => TransactionType::DEBIT,
                'status' => TransactionStatus::SUCCESS,
                'amount' => $amount->decimal(),
                'currency' => $amount->currency->value(),
                'balance_before' => $before->decimal(),
                'balance_after' => $after->decimal(),
                'metadata' => $metadata,
            ]);

            WalletDebited::dispatch($transaction);

            return $transaction;
        });
    }
}
