<?php

namespace App\Domain\Wallet\Services;

use App\Domain\Wallet\Enums\TransactionStatus;
use App\Domain\Wallet\Enums\TransactionType;
use App\Domain\Wallet\Events\WalletRefunded;
use App\Domain\Wallet\Interfaces\WalletRepositoryInterface;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\Models\WalletTransaction;
use App\Domain\Wallet\Services\Concerns\UpdatesWalletAccounts;
use App\Domain\Wallet\Validators\WalletValidationService;
use App\Domain\Wallet\ValueObjects\Money;
use App\Services\BaseService;

class WalletRefundService extends BaseService
{
    use UpdatesWalletAccounts;

    public function __construct(
        private readonly WalletRepositoryInterface $wallets,
        private readonly WalletValidationService $validator,
    ) {
    }

    public function refund(Wallet $wallet, Money $amount, string $reference, ?string $idempotencyKey = null, array $metadata = []): WalletTransaction
    {
        if ($existing = $this->wallets->idempotencyKeyExists($idempotencyKey)) {
            return $existing;
        }

        return $this->transaction(function () use ($wallet, $amount, $reference, $idempotencyKey, $metadata): WalletTransaction {
            $lockedWallet = $this->wallets->findLocked($wallet->id);

            $this->validator->assertRefundIsValid($lockedWallet, $amount);
            $this->validator->assertReferenceIsUnique($reference);

            $availableBefore = Money::fromDecimal($lockedWallet->available_balance, $lockedWallet->currency);
            $refundBefore = Money::fromDecimal($lockedWallet->refund_balance, $lockedWallet->currency);
            $availableAfter = $availableBefore->add($amount);
            $refundAfter = $refundBefore->add($amount);

            $lockedWallet->forceFill([
                'available_balance' => $availableAfter->decimal(),
                'refund_balance' => $refundAfter->decimal(),
            ])->save();

            $this->syncAccount($lockedWallet, 'available', $availableAfter->decimal());
            $this->syncAccount($lockedWallet, 'refund', $refundAfter->decimal());

            $transaction = WalletTransaction::query()->create([
                'tenant_id' => $lockedWallet->tenant_id,
                'wallet_id' => $lockedWallet->id,
                'reference' => $reference,
                'idempotency_key' => $idempotencyKey,
                'type' => TransactionType::REFUND,
                'status' => TransactionStatus::SUCCESS,
                'amount' => $amount->decimal(),
                'currency' => $amount->currency->value(),
                'balance_before' => $availableBefore->decimal(),
                'balance_after' => $availableAfter->decimal(),
                'metadata' => $metadata,
            ]);

            WalletRefunded::dispatch($transaction);

            return $transaction;
        });
    }
}
