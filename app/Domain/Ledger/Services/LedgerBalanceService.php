<?php

namespace App\Domain\Ledger\Services;

use App\Domain\Ledger\Enums\LedgerAccountType;
use App\Domain\Ledger\Enums\LedgerEntryStatus;
use App\Domain\Ledger\Enums\LedgerEntryType;
use App\Domain\Ledger\Interfaces\LedgerQueryInterface;
use App\Domain\Ledger\Models\LedgerAccount;
use App\Domain\Ledger\Models\LedgerEntry;
use App\Domain\Wallet\ValueObjects\Currency;
use App\Domain\Wallet\ValueObjects\Money;

class LedgerBalanceService implements LedgerQueryInterface
{
    public function accountBalance(string $accountId): Money
    {
        $account = LedgerAccount::query()->findOrFail($accountId);
        $debits = $this->sum($accountId, LedgerEntryType::DEBIT);
        $credits = $this->sum($accountId, LedgerEntryType::CREDIT);
        $normalDebit = in_array($account->type, [LedgerAccountType::ASSET, LedgerAccountType::EXPENSE], true);
        $balanceMinorUnits = $normalDebit ? $debits - $credits : $credits - $debits;

        return new Money(max(0, $balanceMinorUnits), new Currency($account->currency));
    }

    public function tenantAccountBalance(?string $tenantId, string $accountCode, string $currency): Money
    {
        $account = LedgerAccount::query()
            ->where('tenant_id', $tenantId)
            ->where('code', $accountCode)
            ->where('currency', strtoupper($currency))
            ->firstOrFail();

        return $this->accountBalance($account->id);
    }

    private function sum(string $accountId, LedgerEntryType $type): int
    {
        $amount = LedgerEntry::query()
            ->where('ledger_account_id', $accountId)
            ->where('type', $type)
            ->where('status', LedgerEntryStatus::POSTED)
            ->sum('amount');

        return (int) round(((float) $amount) * 100);
    }
}
