<?php

namespace Tests\Feature\Ui;

use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Payment\DTOs\PaymentInitializationData;
use App\Domain\Payment\Enums\PaymentProvider;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Interfaces\PaymentServiceInterface;
use App\Domain\Payment\Models\PaymentTransaction;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Services\TenantContext;
use App\Domain\Wallet\Models\Wallet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WalletOperationalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_overview_renders(): void
    {
        [$tenant, $user] = $this->tenantUserWithWallet();

        $this->actingAs($user)
            ->get(route('tenant.wallet.index', ['tenant' => $tenant]))
            ->assertOk()
            ->assertSee('Wallet Overview');
    }

    public function test_transaction_page_renders(): void
    {
        [$tenant, $user] = $this->tenantUserWithWallet();

        $this->actingAs($user)
            ->get(route('tenant.wallet.transactions', ['tenant' => $tenant]))
            ->assertOk()
            ->assertSee('Transaction History');
    }

    public function test_funding_form_renders(): void
    {
        [$tenant, $user] = $this->tenantUserWithWallet();

        $this->actingAs($user)
            ->get(route('tenant.wallet.funding.index', ['tenant' => $tenant]))
            ->assertOk()
            ->assertSee('Wallet Funding Form');
    }

    public function test_funding_validation_fails_correctly(): void
    {
        [$tenant, $user] = $this->tenantUserWithWallet();

        $this->actingAs($user)
            ->from(route('tenant.wallet.funding.index', ['tenant' => $tenant]))
            ->post(route('tenant.wallet.funding.store', ['tenant' => $tenant]), [
                'amount' => 50,
                'currency' => 'USD',
                'email' => 'not-an-email',
            ])
            ->assertRedirect(route('tenant.wallet.funding.index', ['tenant' => $tenant]))
            ->assertSessionHasErrors(['amount', 'currency', 'email']);
    }

    public function test_funding_initialization_calls_payment_platform_boundary(): void
    {
        [$tenant, $user, $wallet] = $this->tenantUserWithWallet();
        $payment = PaymentTransaction::query()->create([
            'tenant_id' => $tenant->id,
            'wallet_id' => $wallet->id,
            'provider' => PaymentProvider::PAYSTACK,
            'reference' => 'PAY-TEST-REFERENCE',
            'amount' => '2500.00',
            'currency' => 'NGN',
            'status' => PaymentStatus::PENDING,
            'authorization_url' => 'https://checkout.example.test/pay',
            'initialized_at' => now(),
        ]);

        $mock = Mockery::mock(PaymentServiceInterface::class);
        $mock->shouldReceive('initialize')
            ->once()
            ->with(Mockery::on(fn (PaymentInitializationData $data): bool => $data->tenantId === $tenant->id
                && $data->wallet->is($wallet)
                && $data->amount->decimal() === '2500.00'
                && $data->email === 'wallet@example.com'))
            ->andReturn($payment);
        $this->app->instance(PaymentServiceInterface::class, $mock);

        $this->actingAs($user)
            ->post(route('tenant.wallet.funding.store', ['tenant' => $tenant]), [
                'amount' => '2500',
                'currency' => 'NGN',
                'email' => 'wallet@example.com',
            ])
            ->assertRedirect(route('tenant.wallet.funding.show', ['tenant' => $tenant, 'reference' => $payment->reference]));
    }

    public function test_payment_initialization_status_page_renders(): void
    {
        [$tenant, $user, $wallet] = $this->tenantUserWithWallet();
        $payment = PaymentTransaction::query()->create([
            'tenant_id' => $tenant->id,
            'wallet_id' => $wallet->id,
            'provider' => PaymentProvider::PAYSTACK,
            'reference' => 'PAY-STATUS-REFERENCE',
            'amount' => '3000.00',
            'currency' => 'NGN',
            'status' => PaymentStatus::PENDING,
            'authorization_url' => 'https://checkout.example.test/pay',
            'initialized_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('tenant.wallet.funding.show', ['tenant' => $tenant, 'reference' => $payment->reference]))
            ->assertOk()
            ->assertSee('Payment Initialization Status')
            ->assertSee('PAY-STATUS-REFERENCE');
    }

    public function test_reservation_page_renders(): void
    {
        [$tenant, $user] = $this->tenantUserWithWallet();

        $this->actingAs($user)
            ->get(route('tenant.wallet.reservations', ['tenant' => $tenant]))
            ->assertOk()
            ->assertSee('Reservation History');
    }

    public function test_wallet_access_without_permission_is_blocked(): void
    {
        $tenant = Tenant::query()->create(['name' => 'No Permission Tenant', 'slug' => 'no-permission', 'status' => 'active']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('tenant.wallet.index', ['tenant' => $tenant]))
            ->assertForbidden();
    }

    private function tenantUserWithWallet(): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $tenant = Tenant::query()->create([
            'name' => 'Wallet Workflow Tenant',
            'slug' => 'wallet-workflow-'.Str::lower((string) Str::ulid()),
            'status' => 'active',
        ]);
        $permission = Permission::query()->firstOrCreate(['name' => 'dashboard.view', 'guard_name' => 'web']);
        app(TenantContext::class)->set($tenant);

        $role = Role::query()->create(['tenant_id' => $tenant->id, 'name' => 'Wallet Operator', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole($role);

        $wallet = Wallet::query()->create([
            'tenant_id' => $tenant->id,
            'currency' => 'NGN',
            'available_balance' => '10000.00',
            'reserved_balance' => '0.00',
            'frozen_balance' => '0.00',
            'status' => 'active',
        ]);

        return [$tenant, $user, $wallet];
    }
}
