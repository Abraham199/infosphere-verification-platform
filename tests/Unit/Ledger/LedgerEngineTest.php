<?php

namespace Tests\Unit\Ledger;

use App\Domain\Ledger\Enums\LedgerAccountType;
use App\Domain\Ledger\Enums\LedgerEntryStatus;
use App\Domain\Ledger\Enums\LedgerEntryType;
use App\Domain\Ledger\Exceptions\DuplicateLedgerReferenceException;
use App\Domain\Ledger\Exceptions\UnbalancedJournalEntryException;
use App\Domain\Ledger\Models\LedgerAccount;
use App\Domain\Ledger\Services\LedgerBalanceService;
use App\Domain\Ledger\Services\LedgerPostingService;
use App\Domain\Ledger\Services\LedgerReconciliationService;
use App\Domain\Ledger\Services\LedgerReversalService;
use App\Domain\Ledger\ValueObjects\JournalEntry;
use App\Domain\Ledger\ValueObjects\LedgerLine;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\ValueObjects\Currency;
use App\Domain\Wallet\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LedgerEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_balanced_journal_entry_can_be_posted(): void
    {
        [$tenant, $asset, $liability] = $this->tenantAccounts();

        $batch = app(LedgerPostingService::class)->post(new JournalEntry(
            tenantId: $tenant->id,
            reference: $this->reference('LEDGER'),
            description: 'Wallet funding accounting entry',
            currency: new Currency('NGN'),
            lines: [
                new LedgerLine($asset->id, LedgerEntryType::DEBIT, Money::fromDecimal('100.00', 'NGN')),
                new LedgerLine($liability->id, LedgerEntryType::CREDIT, Money::fromDecimal('100.00', 'NGN')),
            ],
        ));

        $this->assertSame(LedgerEntryStatus::POSTED, $batch->status);
        $this->assertCount(2, $batch->load('entries')->entries);
    }

    public function test_unbalanced_journal_entry_is_rejected(): void
    {
        [$tenant, $asset, $liability] = $this->tenantAccounts();

        $this->expectException(UnbalancedJournalEntryException::class);

        app(LedgerPostingService::class)->post(new JournalEntry(
            tenantId: $tenant->id,
            reference: $this->reference('UNBALANCED'),
            description: 'Invalid journal',
            currency: new Currency('NGN'),
            lines: [
                new LedgerLine($asset->id, LedgerEntryType::DEBIT, Money::fromDecimal('100.00', 'NGN')),
                new LedgerLine($liability->id, LedgerEntryType::CREDIT, Money::fromDecimal('90.00', 'NGN')),
            ],
        ));
    }

    public function test_duplicate_batch_reference_is_rejected(): void
    {
        [$tenant, $asset, $liability] = $this->tenantAccounts();
        $reference = $this->reference('DUPLICATE');
        $journal = fn () => new JournalEntry(
            tenantId: $tenant->id,
            reference: $reference,
            description: 'Duplicate test',
            currency: new Currency('NGN'),
            lines: [
                new LedgerLine($asset->id, LedgerEntryType::DEBIT, Money::fromDecimal('10.00', 'NGN')),
                new LedgerLine($liability->id, LedgerEntryType::CREDIT, Money::fromDecimal('10.00', 'NGN')),
            ],
        );

        app(LedgerPostingService::class)->post($journal());

        $this->expectException(DuplicateLedgerReferenceException::class);

        app(LedgerPostingService::class)->post($journal());
    }

    public function test_ledger_balance_calculation_respects_account_normal_balance(): void
    {
        [$tenant, $asset, $liability] = $this->tenantAccounts();
        app(LedgerPostingService::class)->post(new JournalEntry(
            tenantId: $tenant->id,
            reference: $this->reference('BALANCE'),
            description: 'Balance test',
            currency: new Currency('NGN'),
            lines: [
                new LedgerLine($asset->id, LedgerEntryType::DEBIT, Money::fromDecimal('50.00', 'NGN')),
                new LedgerLine($liability->id, LedgerEntryType::CREDIT, Money::fromDecimal('50.00', 'NGN')),
            ],
        ));

        $this->assertSame('50.00', app(LedgerBalanceService::class)->accountBalance($asset->id)->decimal());
        $this->assertSame('50.00', app(LedgerBalanceService::class)->accountBalance($liability->id)->decimal());
    }

    public function test_posted_batch_can_be_reversed_with_append_only_entries(): void
    {
        [$tenant, $asset, $liability] = $this->tenantAccounts();
        $batch = app(LedgerPostingService::class)->post(new JournalEntry(
            tenantId: $tenant->id,
            reference: $this->reference('REVERSIBLE'),
            description: 'Reversible test',
            currency: new Currency('NGN'),
            lines: [
                new LedgerLine($asset->id, LedgerEntryType::DEBIT, Money::fromDecimal('20.00', 'NGN')),
                new LedgerLine($liability->id, LedgerEntryType::CREDIT, Money::fromDecimal('20.00', 'NGN')),
            ],
        ));

        $reversal = app(LedgerReversalService::class)->reverse($batch, 'Test correction');

        $this->assertSame($batch->id, $reversal->reversal_of_batch_id);
        $this->assertCount(2, $reversal->load('entries')->entries);
    }

    public function test_wallet_reconciliation_detects_matching_wallet_liability(): void
    {
        [$tenant, $asset, $liability] = $this->tenantAccounts();
        $wallet = Wallet::query()->create([
            'tenant_id' => $tenant->id,
            'currency' => 'NGN',
            'available_balance' => '100.00',
            'status' => 'active',
        ]);

        app(LedgerPostingService::class)->post(new JournalEntry(
            tenantId: $tenant->id,
            reference: $this->reference('REC'),
            description: 'Reconciliation fixture',
            currency: new Currency('NGN'),
            lines: [
                new LedgerLine($asset->id, LedgerEntryType::DEBIT, Money::fromDecimal('100.00', 'NGN')),
                new LedgerLine($liability->id, LedgerEntryType::CREDIT, Money::fromDecimal('100.00', 'NGN')),
            ],
        ));

        $run = app(LedgerReconciliationService::class)->reconcileWallet($tenant->id, $wallet->id);

        $this->assertSame('matched', $run->status->value);
    }

    private function tenantAccounts(): array
    {
        $tenant = Tenant::query()->create([
            'name' => 'Ledger Test Tenant',
            'slug' => 'ledger-test-'.Str::lower((string) Str::ulid()),
            'status' => 'active',
        ]);

        $asset = LedgerAccount::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'cash_clearing',
            'name' => 'Cash Clearing',
            'type' => LedgerAccountType::ASSET,
            'currency' => 'NGN',
            'is_active' => true,
        ]);

        $liability = LedgerAccount::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'tenant_wallet_liability',
            'name' => 'Tenant Wallet Liability',
            'type' => LedgerAccountType::LIABILITY,
            'currency' => 'NGN',
            'is_active' => true,
        ]);

        return [$tenant, $asset, $liability];
    }

    private function reference(string $prefix): string
    {
        return strtoupper($prefix).'-'.strtoupper((string) Str::ulid());
    }
}
