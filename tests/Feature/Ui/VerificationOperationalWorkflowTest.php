<?php

namespace Tests\Feature\Ui;

use App\Contracts\Verification\VerificationProviderContract;
use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Ledger\Enums\LedgerAccountType;
use App\Domain\Ledger\Models\LedgerAccount;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Services\TenantContext;
use App\Domain\Verification\DTOs\VerificationProviderResponse;
use App\Domain\Verification\DTOs\VerificationRequestData;
use App\Domain\Verification\Enums\VerificationProvider;
use App\Domain\Verification\Interfaces\VerificationProviderManagerInterface;
use App\Domain\Verification\Interfaces\VerificationServiceInterface;
use App\Domain\Verification\Models\ProviderService;
use App\Domain\Verification\Models\VerificationPricingRule;
use App\Domain\Verification\Models\VerificationRequest;
use App\Domain\Verification\Models\VerificationResult;
use App\Domain\Verification\Models\VerificationService;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\Services\WalletCreditService;
use App\Domain\Wallet\ValueObjects\Money;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class VerificationOperationalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_page_renders(): void
    {
        [$tenant, $user] = $this->fixture();

        $this->actingAs($user)
            ->get(route('tenant.verification.index', ['tenant' => $tenant]))
            ->assertOk()
            ->assertSee('Verification Request');
    }

    public function test_service_list_renders_and_pricing_displays(): void
    {
        [$tenant, $user, , $service] = $this->fixture();
        VerificationPricingRule::query()->create([
            'tenant_id' => $tenant->id,
            'verification_service_id' => $service->id,
            'price' => '125.00',
            'currency' => 'NGN',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('tenant.verification.services', ['tenant' => $tenant]))
            ->assertOk()
            ->assertSee('Available Services')
            ->assertSee('NGN 125.00');
    }

    public function test_validation_errors_are_displayed(): void
    {
        [$tenant, $user] = $this->fixture();

        $this->actingAs($user)
            ->from(route('tenant.verification.index', ['tenant' => $tenant]))
            ->post(route('tenant.verification.request.store', ['tenant' => $tenant]), [
                'service_code' => '',
                'subject_identifier' => 'A',
                'customer_email' => 'bad-email',
            ])
            ->assertRedirect(route('tenant.verification.index', ['tenant' => $tenant]))
            ->assertSessionHasErrors(['service_code', 'subject_identifier', 'customer_email']);
    }

    public function test_wallet_balance_validation_blocks_submission(): void
    {
        [$tenant, $user, $wallet] = $this->fixture(walletBalance: '10.00');

        $this->actingAs($user)
            ->from(route('tenant.verification.index', ['tenant' => $tenant]))
            ->post(route('tenant.verification.request.store', ['tenant' => $tenant]), [
                'service_code' => 'nin_lookup',
                'subject_identifier' => '12345678901',
            ])
            ->assertRedirect(route('tenant.verification.index', ['tenant' => $tenant]))
            ->assertSessionHasErrors(['wallet']);

        $this->assertSame('10.00', $wallet->refresh()->available_balance);
    }

    public function test_submission_calls_verification_service_boundary(): void
    {
        [$tenant, $user, $wallet, $service] = $this->fixture();
        $verification = VerificationRequest::query()->create([
            'tenant_id' => $tenant->id,
            'wallet_id' => $wallet->id,
            'verification_service_id' => $service->id,
            'reference' => 'VER-BOUNDARY',
            'price_charged' => '50.00',
            'currency' => 'NGN',
            'status' => 'submitted',
        ]);

        $mock = Mockery::mock(VerificationServiceInterface::class);
        $mock->shouldReceive('request')
            ->once()
            ->with(Mockery::on(fn (VerificationRequestData $data): bool => $data->tenantId === $tenant->id
                && $data->wallet->is($wallet)
                && $data->serviceCode === 'nin_lookup'
                && $data->subjectIdentifier === '12345678901'))
            ->andReturn($verification);
        $this->app->instance(VerificationServiceInterface::class, $mock);

        $this->actingAs($user)
            ->post(route('tenant.verification.request.store', ['tenant' => $tenant]), [
                'service_code' => 'nin_lookup',
                'subject_identifier' => '12345678901',
            ])
            ->assertRedirect(route('tenant.verification.show', ['tenant' => $tenant, 'reference' => 'VER-BOUNDARY']));
    }

    public function test_successful_submission_creates_reservation_debit_and_result_through_domain(): void
    {
        [$tenant, $user, $wallet] = $this->fixture();
        $this->bindFakeProvider(successful: true);

        $this->actingAs($user)
            ->post(route('tenant.verification.request.store', ['tenant' => $tenant]), [
                'service_code' => 'nin_lookup',
                'subject_identifier' => '12345678901',
                'customer_name' => 'Ada Customer',
            ])
            ->assertRedirect();

        $request = VerificationRequest::query()->where('tenant_id', $tenant->id)->firstOrFail();

        $this->assertNotNull($request->wallet_reservation_id);
        $this->assertNotNull($request->wallet_transaction_id);
        $this->assertNotNull($request->ledger_batch_id);
        $this->assertSame('450.00', $wallet->refresh()->available_balance);
        $this->assertDatabaseHas('verification_results', ['verification_request_id' => $request->id]);
    }

    public function test_history_status_and_result_pages_render(): void
    {
        [$tenant, $user, $wallet, $service] = $this->fixture();
        $request = VerificationRequest::query()->create([
            'tenant_id' => $tenant->id,
            'wallet_id' => $wallet->id,
            'verification_service_id' => $service->id,
            'reference' => 'VER-HISTORY',
            'price_charged' => '50.00',
            'currency' => 'NGN',
            'status' => 'completed',
        ]);
        VerificationResult::query()->create([
            'tenant_id' => $tenant->id,
            'verification_request_id' => $request->id,
            'result_status' => 'match',
            'confidence_score' => 99.5,
            'summary' => 'Verified',
            'normalized_data' => ['name' => 'Masked User'],
        ]);

        $this->actingAs($user)->get(route('tenant.verification.history', ['tenant' => $tenant]))->assertOk()->assertSee('VER-HISTORY');
        $this->actingAs($user)->get(route('tenant.verification.show', ['tenant' => $tenant, 'reference' => 'VER-HISTORY']))->assertOk()->assertSee('Verification Status');
        $this->actingAs($user)->get(route('tenant.verification.result', ['tenant' => $tenant, 'reference' => 'VER-HISTORY']))->assertOk()->assertSee('Verified');
    }

    public function test_unauthorized_access_is_blocked(): void
    {
        $tenant = Tenant::query()->create(['name' => 'Blocked Tenant', 'slug' => 'blocked-'.Str::lower((string) Str::ulid()), 'status' => 'active']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('tenant.verification.index', ['tenant' => $tenant]))
            ->assertForbidden();
    }

    public function test_tenant_isolation_blocks_other_tenant_result(): void
    {
        [$tenant, $user] = $this->fixture();
        [$otherTenant, , $otherWallet, $otherService] = $this->fixture(slugPrefix: 'other-verification', serviceCode: 'other_nin_lookup');
        VerificationRequest::query()->create([
            'tenant_id' => $otherTenant->id,
            'wallet_id' => $otherWallet->id,
            'verification_service_id' => $otherService->id,
            'reference' => 'VER-OTHER',
            'price_charged' => '50.00',
            'currency' => 'NGN',
            'status' => 'completed',
        ]);

        $this->actingAs($user)
            ->get(route('tenant.verification.show', ['tenant' => $tenant, 'reference' => 'VER-OTHER']))
            ->assertNotFound();
    }

    private function fixture(string $walletBalance = '500.00', string $slugPrefix = 'verification-workflow', string $serviceCode = 'nin_lookup'): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $tenant = Tenant::query()->create([
            'name' => 'Verification Workflow Tenant',
            'slug' => $slugPrefix.'-'.Str::lower((string) Str::ulid()),
            'status' => 'active',
        ]);
        app(TenantContext::class)->set($tenant);

        $permission = Permission::query()->firstOrCreate(['name' => 'dashboard.view', 'guard_name' => 'web']);
        $role = Role::query()->create(['tenant_id' => $tenant->id, 'name' => 'Verification Operator '.$tenant->id, 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole($role);

        $wallet = Wallet::query()->create([
            'tenant_id' => $tenant->id,
            'currency' => 'NGN',
            'available_balance' => '0.00',
            'reserved_balance' => '0.00',
            'frozen_balance' => '0.00',
            'status' => 'active',
        ]);

        if ((float) $walletBalance > 0) {
            app(WalletCreditService::class)->credit($wallet, Money::fromDecimal($walletBalance, 'NGN'), 'TEST-FUND-'.strtoupper((string) Str::ulid()));
        }

        $service = VerificationService::query()->create([
            'name' => 'NIN Lookup',
            'slug' => 'nin-lookup-'.$tenant->slug,
            'service_code' => $serviceCode,
            'default_price' => '50.00',
            'global_price' => '50.00',
            'currency' => 'NGN',
            'status' => 'active',
        ]);
        ProviderService::query()->create([
            'verification_service_id' => $service->id,
            'provider' => VerificationProvider::SWIFTVERIFY,
            'provider_service_code' => 'swift_'.$serviceCode,
            'status' => 'active',
            'priority' => 1,
        ]);

        foreach ([['tenant_wallet_liability', 'Tenant Wallet Liability', LedgerAccountType::LIABILITY], ['platform_verification_revenue', 'Platform Verification Revenue', LedgerAccountType::REVENUE]] as [$code, $name, $type]) {
            LedgerAccount::query()->create(['tenant_id' => $tenant->id, 'code' => $code, 'name' => $name, 'type' => $type, 'currency' => 'NGN', 'is_active' => true]);
        }

        return [$tenant, $user, $wallet, $service];
    }

    private function bindFakeProvider(bool $successful): void
    {
        $provider = new class($successful) implements VerificationProviderContract {
            public function __construct(private readonly bool $successful) {}
            public function supports(string $providerServiceCode): bool { return true; }
            public function submit(string $providerServiceCode, array $payload, string $reference): VerificationProviderResponse
            {
                return new VerificationProviderResponse($this->successful, $this->successful ? 'completed' : 'failed', 'swift-'.$reference, $this->successful ? 'match' : 'error', 99.5, $this->successful ? 'Verified' : 'Failed', ['name' => 'Masked'], ['status' => $this->successful], $this->successful ? null : 'Provider failure');
            }
            public function normalize(array $providerResponse): VerificationProviderResponse { return new VerificationProviderResponse(true, 'completed'); }
        };

        $this->app->bind(VerificationProviderManagerInterface::class, fn () => new class($provider) implements VerificationProviderManagerInterface {
            public function __construct(private readonly VerificationProviderContract $provider) {}
            public function driver(string $provider): VerificationProviderContract { return $this->provider; }
        });
    }
}
