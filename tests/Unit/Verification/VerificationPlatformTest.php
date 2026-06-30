<?php

namespace Tests\Unit\Verification;

use App\Contracts\Verification\VerificationProviderContract;
use App\Domain\Ledger\Enums\LedgerAccountType;
use App\Domain\Ledger\Models\LedgerAccount;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Verification\DTOs\VerificationProviderResponse;
use App\Domain\Verification\DTOs\VerificationRequestData;
use App\Domain\Verification\Enums\VerificationProvider;
use App\Domain\Verification\Enums\VerificationStatus;
use App\Domain\Verification\Exceptions\DuplicateVerificationRequestException;
use App\Domain\Verification\Interfaces\VerificationProviderManagerInterface;
use App\Domain\Verification\Models\ProviderService;
use App\Domain\Verification\Models\VerificationPricingRule;
use App\Domain\Verification\Models\VerificationService as CatalogService;
use App\Domain\Verification\Services\VerificationPricingService;
use App\Domain\Verification\Services\VerificationService;
use App\Domain\Wallet\Services\WalletCreationService;
use App\Domain\Wallet\Services\WalletCreditService;
use App\Domain\Wallet\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VerificationPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_pricing_resolution_prefers_tenant_pricing(): void
    {
        [$tenant, , $service] = $this->fixture();
        VerificationPricingRule::query()->create([
            'tenant_id' => $tenant->id,
            'verification_service_id' => $service->id,
            'price' => '120.00',
            'currency' => 'NGN',
            'status' => 'active',
        ]);

        $price = app(VerificationPricingService::class)->resolve($tenant->id, $service);

        $this->assertSame('120.00', $price->decimal());
    }

    public function test_successful_verification_debits_wallet_and_posts_ledger(): void
    {
        [$tenant, $wallet] = $this->fixture();
        $this->bindFakeProvider(successful: true);
        app(WalletCreditService::class)->credit($wallet, Money::fromDecimal('500.00', 'NGN'), $this->reference('FUND'));

        $request = app(VerificationService::class)->request(new VerificationRequestData(
            tenantId: $tenant->id,
            wallet: $wallet,
            serviceCode: 'nin_lookup',
            reference: $this->reference('VER'),
            payload: ['nin' => '12345678901'],
            subjectIdentifier: '12345678901',
            idempotencyKey: 'verify-once',
        ));

        $this->assertSame(VerificationStatus::COMPLETED, $request->status);
        $this->assertSame('450.00', $wallet->refresh()->available_balance);
        $this->assertNotNull($request->wallet_transaction_id);
        $this->assertNotNull($request->ledger_batch_id);
        $this->assertNotNull($request->load('result')->result);
    }

    public function test_failed_verification_releases_reservation_without_debit(): void
    {
        [$tenant, $wallet] = $this->fixture();
        $this->bindFakeProvider(successful: false);
        app(WalletCreditService::class)->credit($wallet, Money::fromDecimal('500.00', 'NGN'), $this->reference('FUND'));

        $request = app(VerificationService::class)->request(new VerificationRequestData(
            tenantId: $tenant->id,
            wallet: $wallet,
            serviceCode: 'nin_lookup',
            reference: $this->reference('VER'),
            payload: ['nin' => '12345678901'],
        ));

        $this->assertSame(VerificationStatus::FAILED, $request->status);
        $this->assertSame('500.00', $wallet->refresh()->available_balance);
        $this->assertSame('0.00', $wallet->reserved_balance);
    }

    public function test_duplicate_idempotency_returns_existing_request(): void
    {
        [$tenant, $wallet] = $this->fixture();
        $this->bindFakeProvider(successful: true);
        app(WalletCreditService::class)->credit($wallet, Money::fromDecimal('500.00', 'NGN'), $this->reference('FUND'));
        $data = new VerificationRequestData($tenant->id, $wallet, 'nin_lookup', $this->reference('VER'), ['nin' => '123'], idempotencyKey: 'same-request');

        $first = app(VerificationService::class)->request($data);
        $second = app(VerificationService::class)->request($data);

        $this->assertSame($first->id, $second->id);
    }

    public function test_duplicate_reference_is_rejected(): void
    {
        [$tenant, $wallet] = $this->fixture();
        $this->bindFakeProvider(successful: true);
        app(WalletCreditService::class)->credit($wallet, Money::fromDecimal('500.00', 'NGN'), $this->reference('FUND'));
        $reference = $this->reference('VER');

        app(VerificationService::class)->request(new VerificationRequestData($tenant->id, $wallet, 'nin_lookup', $reference, ['nin' => '123']));

        $this->expectException(DuplicateVerificationRequestException::class);

        app(VerificationService::class)->request(new VerificationRequestData($tenant->id, $wallet, 'nin_lookup', $reference, ['nin' => '123']));
    }

    private function fixture(): array
    {
        $tenant = Tenant::query()->create(['name' => 'Verification Tenant', 'slug' => 'verification-'.Str::lower((string) Str::ulid()), 'status' => 'active']);
        $wallet = app(WalletCreationService::class)->createForTenant($tenant, 'NGN');
        $service = CatalogService::query()->create([
            'name' => 'NIN Lookup',
            'slug' => 'nin-lookup',
            'service_code' => 'nin_lookup',
            'default_price' => '50.00',
            'global_price' => '50.00',
            'currency' => 'NGN',
            'status' => 'active',
        ]);
        ProviderService::query()->create([
            'verification_service_id' => $service->id,
            'provider' => VerificationProvider::SWIFTVERIFY,
            'provider_service_code' => 'swift_nin',
            'status' => 'active',
            'priority' => 1,
        ]);

        foreach ([['tenant_wallet_liability', 'Tenant Wallet Liability', LedgerAccountType::LIABILITY], ['platform_verification_revenue', 'Platform Verification Revenue', LedgerAccountType::REVENUE]] as [$code, $name, $type]) {
            LedgerAccount::query()->create(['tenant_id' => $tenant->id, 'code' => $code, 'name' => $name, 'type' => $type, 'currency' => 'NGN', 'is_active' => true]);
        }

        return [$tenant, $wallet, $service];
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

    private function reference(string $prefix): string
    {
        return strtoupper($prefix).'-'.strtoupper((string) Str::ulid());
    }
}
