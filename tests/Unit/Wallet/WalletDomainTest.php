<?php

namespace Tests\Unit\Wallet;

use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Wallet\Enums\ReservationStatus;
use App\Domain\Wallet\Enums\WalletStatus;
use App\Domain\Wallet\Exceptions\DuplicateTransactionReferenceException;
use App\Domain\Wallet\Exceptions\InsufficientFundsException;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\Services\WalletAdjustmentService;
use App\Domain\Wallet\Services\WalletBalanceService;
use App\Domain\Wallet\Services\WalletCreationService;
use App\Domain\Wallet\Services\WalletCreditService;
use App\Domain\Wallet\Services\WalletDebitService;
use App\Domain\Wallet\Services\WalletFreezeService;
use App\Domain\Wallet\Services\WalletRefundService;
use App\Domain\Wallet\Services\WalletReservationService;
use App\Domain\Wallet\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WalletDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_creation_creates_wallet_and_balance_accounts(): void
    {
        $tenant = $this->tenant();

        $wallet = app(WalletCreationService::class)->createForTenant($tenant, 'NGN');

        $this->assertSame($tenant->id, $wallet->tenant_id);
        $this->assertSame('NGN', $wallet->currency);
        $this->assertCount(5, $wallet->accounts);
    }

    public function test_wallet_credit_increases_available_balance(): void
    {
        $wallet = $this->wallet();

        app(WalletCreditService::class)->credit($wallet, Money::fromDecimal('100.00', 'NGN'), $this->reference('CREDIT'));

        $this->assertSame('100.00', $wallet->refresh()->available_balance);
    }

    public function test_wallet_debit_decreases_available_balance(): void
    {
        $wallet = $this->wallet();
        app(WalletCreditService::class)->credit($wallet, Money::fromDecimal('100.00', 'NGN'), $this->reference('CREDIT'));

        app(WalletDebitService::class)->debit($wallet, Money::fromDecimal('35.00', 'NGN'), $this->reference('DEBIT'));

        $this->assertSame('65.00', $wallet->refresh()->available_balance);
    }

    public function test_wallet_reservation_moves_available_to_reserved(): void
    {
        $wallet = $this->wallet();
        app(WalletCreditService::class)->credit($wallet, Money::fromDecimal('100.00', 'NGN'), $this->reference('CREDIT'));

        $reservation = app(WalletReservationService::class)->reserve($wallet, Money::fromDecimal('40.00', 'NGN'), $this->reference('RESERVE'));

        $this->assertSame(ReservationStatus::ACTIVE, $reservation->status);
        $this->assertSame('60.00', $wallet->refresh()->available_balance);
        $this->assertSame('40.00', $wallet->reserved_balance);
    }

    public function test_wallet_release_returns_reserved_funds_to_available_balance(): void
    {
        $wallet = $this->wallet();
        app(WalletCreditService::class)->credit($wallet, Money::fromDecimal('100.00', 'NGN'), $this->reference('CREDIT'));
        $reservation = app(WalletReservationService::class)->reserve($wallet, Money::fromDecimal('40.00', 'NGN'), $this->reference('RESERVE'));

        app(WalletReservationService::class)->release($reservation);

        $this->assertSame('100.00', $wallet->refresh()->available_balance);
        $this->assertSame('0.00', $wallet->reserved_balance);
    }

    public function test_wallet_refund_increases_available_and_refund_balances(): void
    {
        $wallet = $this->wallet();

        app(WalletRefundService::class)->refund($wallet, Money::fromDecimal('25.00', 'NGN'), $this->reference('REFUND'));

        $this->assertSame('25.00', $wallet->refresh()->available_balance);
        $this->assertSame('25.00', $wallet->refund_balance);
    }

    public function test_wallet_freeze_moves_available_to_frozen_balance(): void
    {
        $wallet = $this->wallet();
        app(WalletCreditService::class)->credit($wallet, Money::fromDecimal('100.00', 'NGN'), $this->reference('CREDIT'));

        app(WalletFreezeService::class)->freeze($wallet, Money::fromDecimal('30.00', 'NGN'), 'Risk review');

        $wallet->refresh();
        $this->assertSame(WalletStatus::FROZEN, $wallet->status);
        $this->assertSame('70.00', $wallet->available_balance);
        $this->assertSame('30.00', $wallet->frozen_balance);
    }

    public function test_balance_calculation_returns_value_object(): void
    {
        $wallet = $this->wallet();
        app(WalletCreditService::class)->credit($wallet, Money::fromDecimal('100.00', 'NGN'), $this->reference('CREDIT'));

        $balance = app(WalletBalanceService::class)->calculate($wallet->refresh());

        $this->assertSame('100.00', $balance->available->decimal());
    }

    public function test_debit_fails_when_available_balance_is_insufficient(): void
    {
        $this->expectException(InsufficientFundsException::class);

        app(WalletDebitService::class)->debit($this->wallet(), Money::fromDecimal('10.00', 'NGN'), $this->reference('DEBIT'));
    }

    public function test_duplicate_transaction_reference_is_rejected(): void
    {
        $wallet = $this->wallet();
        $reference = $this->reference('DUPLICATE');
        app(WalletCreditService::class)->credit($wallet, Money::fromDecimal('10.00', 'NGN'), $reference);

        $this->expectException(DuplicateTransactionReferenceException::class);

        app(WalletCreditService::class)->credit($wallet, Money::fromDecimal('10.00', 'NGN'), $reference);
    }

    public function test_wallet_adjustment_can_credit_available_balance(): void
    {
        $wallet = $this->wallet();

        app(WalletAdjustmentService::class)->apply($wallet, Money::fromDecimal('15.00', 'NGN'), 'credit', $this->reference('ADJUST'), 'Opening correction');

        $this->assertSame('15.00', $wallet->refresh()->available_balance);
    }

    private function tenant(): Tenant
    {
        return Tenant::query()->create([
            'name' => 'Wallet Test Tenant',
            'slug' => 'wallet-test-'.Str::lower((string) Str::ulid()),
            'status' => 'active',
        ]);
    }

    private function wallet(): Wallet
    {
        return app(WalletCreationService::class)->createForTenant($this->tenant(), 'NGN');
    }

    private function reference(string $prefix): string
    {
        return strtoupper($prefix).'-'.strtoupper((string) Str::ulid());
    }
}
