<?php

namespace App\Domain\Ledger\Services;

use App\Domain\Ledger\Enums\ReconciliationStatus;
use App\Domain\Ledger\Events\LedgerReconciled;
use App\Domain\Ledger\Interfaces\LedgerReconciliationInterface;
use App\Domain\Ledger\Models\LedgerAccount;
use App\Domain\Ledger\Models\ReconciliationRun;
use App\Domain\Ledger\Models\ReconciliationResult;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\ValueObjects\Money;
use App\Services\BaseService;
use Illuminate\Support\Str;

class LedgerReconciliationService extends BaseService implements LedgerReconciliationInterface
{
    public function __construct(private readonly LedgerBalanceService $balances)
    {
    }

    public function reconcileWallet(string $tenantId, string $walletId): ReconciliationRun
    {
        return $this->transaction(function () use ($tenantId, $walletId): ReconciliationRun {
            $wallet = Wallet::query()
                ->where('tenant_id', $tenantId)
                ->whereKey($walletId)
                ->firstOrFail();

            $run = ReconciliationRun::query()->create([
                'tenant_id' => $tenantId,
                'reference' => 'REC-WALLET-'.strtoupper((string) Str::ulid()),
                'scope' => 'wallet',
                'status' => ReconciliationStatus::PENDING,
                'started_at' => now(),
                'metadata' => ['wallet_id' => $wallet->id],
            ]);

            $account = LedgerAccount::query()
                ->where('tenant_id', $tenantId)
                ->where('code', 'tenant_wallet_liability')
                ->where('currency', $wallet->currency)
                ->first();

            $walletBalance = Money::fromDecimal($wallet->available_balance, $wallet->currency)
                ->add(Money::fromDecimal($wallet->reserved_balance, $wallet->currency))
                ->add(Money::fromDecimal($wallet->frozen_balance, $wallet->currency));

            $ledgerBalance = $account
                ? $this->balances->accountBalance($account->id)
                : Money::fromDecimal('0.00', $wallet->currency);

            $differenceMinorUnits = abs($walletBalance->minorUnits - $ledgerBalance->minorUnits);
            $status = $differenceMinorUnits === 0
                ? ReconciliationStatus::MATCHED
                : ReconciliationStatus::DIFFERENCE_FOUND;

            ReconciliationResult::query()->create([
                'tenant_id' => $tenantId,
                'reconciliation_run_id' => $run->id,
                'source_type' => Wallet::class,
                'source_reference' => $wallet->id,
                'status' => $status,
                'expected_amount' => $walletBalance->decimal(),
                'actual_amount' => $ledgerBalance->decimal(),
                'difference_amount' => number_format($differenceMinorUnits / 100, 2, '.', ''),
                'currency' => $wallet->currency,
                'notes' => $account ? null : 'Tenant wallet liability ledger account is missing.',
            ]);

            $run->forceFill([
                'status' => $status,
                'completed_at' => now(),
            ])->save();

            LedgerReconciled::dispatch($run);

            return $run->refresh();
        });
    }
}
