<?php

namespace Tests\Unit\Payment;

use App\Contracts\Payments\PaymentProviderContract;
use App\Domain\Ledger\Enums\LedgerAccountType;
use App\Domain\Ledger\Models\LedgerAccount;
use App\Domain\Payment\DTOs\PaymentInitializationData;
use App\Domain\Payment\DTOs\PaymentProviderResponse;
use App\Domain\Payment\DTOs\PaymentWebhookData;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Exceptions\InvalidPaymentStateException;
use App\Domain\Payment\Interfaces\PaymentProviderManagerInterface;
use App\Domain\Payment\Models\PaymentWebhook;
use App\Domain\Payment\Services\PaymentService;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Wallet\Models\WalletTransaction;
use App\Domain\Wallet\Services\WalletCreationService;
use App\Domain\Wallet\ValueObjects\Money;
use App\Infrastructure\Paystack\PaystackAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_initialization_uses_provider_abstraction(): void
    {
        [$tenant, $wallet] = $this->tenantWalletAndLedgerAccounts();
        $this->bindFakeProvider();

        $payment = app(PaymentService::class)->initialize(new PaymentInitializationData(
            tenantId: $tenant->id,
            wallet: $wallet,
            amount: Money::fromDecimal('100.00', 'NGN'),
            email: 'customer@example.test',
            reference: $this->reference('PAY'),
            idempotencyKey: 'idem-init',
        ));

        $this->assertSame(PaymentStatus::PENDING, $payment->status);
        $this->assertNotNull($payment->authorization_url);
    }

    public function test_paystack_webhook_signature_verification(): void
    {
        config()->set('services.paystack.secret_key', 'test-secret');
        $payload = '{"event":"charge.success"}';
        $signature = hash_hmac('sha512', $payload, 'test-secret');

        $this->assertTrue(app(PaystackAdapter::class)->verifyWebhookSignature($payload, $signature));
        $this->assertFalse(app(PaystackAdapter::class)->verifyWebhookSignature($payload, 'bad-signature'));
    }

    public function test_successful_payment_verification_credits_wallet_and_posts_ledger(): void
    {
        [$tenant, $wallet] = $this->tenantWalletAndLedgerAccounts();
        $this->bindFakeProvider(verifySuccessful: true);
        $reference = $this->reference('PAY');

        app(PaymentService::class)->initialize(new PaymentInitializationData(
            tenantId: $tenant->id,
            wallet: $wallet,
            amount: Money::fromDecimal('75.00', 'NGN'),
            email: 'customer@example.test',
            reference: $reference,
        ));

        $payment = app(PaymentService::class)->verify($reference);

        $this->assertSame(PaymentStatus::SUCCESS, $payment->status);
        $this->assertSame('75.00', $wallet->refresh()->available_balance);
        $this->assertNotNull($payment->wallet_transaction_id);
        $this->assertNotNull($payment->ledger_batch_id);
    }

    public function test_failed_payment_verification_marks_payment_failed_without_wallet_credit(): void
    {
        [$tenant, $wallet] = $this->tenantWalletAndLedgerAccounts();
        $this->bindFakeProvider(verifySuccessful: false);
        $reference = $this->reference('PAY');

        app(PaymentService::class)->initialize(new PaymentInitializationData(
            tenantId: $tenant->id,
            wallet: $wallet,
            amount: Money::fromDecimal('50.00', 'NGN'),
            email: 'customer@example.test',
            reference: $reference,
        ));

        $payment = app(PaymentService::class)->verify($reference);

        $this->assertSame(PaymentStatus::FAILED, $payment->status);
        $this->assertSame('0.00', $wallet->refresh()->available_balance);
    }

    public function test_webhook_processing_is_idempotent_against_duplicate_event_reference(): void
    {
        [$tenant, $wallet] = $this->tenantWalletAndLedgerAccounts();
        $this->bindFakeProvider(verifySuccessful: true, signatureValid: true);
        $reference = $this->reference('PAY');
        $rawPayload = json_encode(['event' => 'charge.success', 'data' => ['reference' => $reference]]);

        app(PaymentService::class)->initialize(new PaymentInitializationData(
            tenantId: $tenant->id,
            wallet: $wallet,
            amount: Money::fromDecimal('30.00', 'NGN'),
            email: 'customer@example.test',
            reference: $reference,
        ));

        $webhook = new PaymentWebhookData(
            provider: 'paystack',
            eventType: 'charge.success',
            eventReference: 'evt-123',
            transactionReference: $reference,
            rawPayload: $rawPayload,
            payload: json_decode($rawPayload, true),
            signature: 'valid',
        );

        app(PaymentService::class)->handleWebhook($webhook);

        try {
            app(PaymentService::class)->handleWebhook($webhook);
            $this->fail('Duplicate webhook was not rejected.');
        } catch (InvalidPaymentStateException) {
            $this->assertTrue(true);
        }

        $this->assertSame(1, PaymentWebhook::query()->where('event_reference', 'evt-123')->count());
        $this->assertSame(1, WalletTransaction::query()->count());
    }

    private function tenantWalletAndLedgerAccounts(): array
    {
        $tenant = Tenant::query()->create([
            'name' => 'Payment Test Tenant',
            'slug' => 'payment-test-'.Str::lower((string) Str::ulid()),
            'status' => 'active',
        ]);
        $wallet = app(WalletCreationService::class)->createForTenant($tenant, 'NGN');

        LedgerAccount::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'cash_clearing',
            'name' => 'Cash Clearing',
            'type' => LedgerAccountType::ASSET,
            'currency' => 'NGN',
            'is_active' => true,
        ]);

        LedgerAccount::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'tenant_wallet_liability',
            'name' => 'Tenant Wallet Liability',
            'type' => LedgerAccountType::LIABILITY,
            'currency' => 'NGN',
            'is_active' => true,
        ]);

        return [$tenant, $wallet];
    }

    private function bindFakeProvider(bool $verifySuccessful = true, bool $signatureValid = true): void
    {
        $provider = new class($verifySuccessful, $signatureValid) implements PaymentProviderContract {
            public function __construct(private readonly bool $verifySuccessful, private readonly bool $signatureValid)
            {
            }

            public function initialize(array $payload): PaymentProviderResponse
            {
                return new PaymentProviderResponse(
                    successful: true,
                    status: 'initialized',
                    providerReference: $payload['reference'],
                    authorizationUrl: 'https://checkout.paystack.test/'.$payload['reference'],
                    payload: ['status' => true, 'data' => ['reference' => $payload['reference']]],
                );
            }

            public function verify(string $reference): PaymentProviderResponse
            {
                return new PaymentProviderResponse(
                    successful: $this->verifySuccessful,
                    status: $this->verifySuccessful ? 'success' : 'failed',
                    providerReference: $reference,
                    paymentMethod: 'card',
                    paidAt: now()->toIso8601String(),
                    failureReason: $this->verifySuccessful ? null : 'Provider declined payment.',
                    payload: ['status' => $this->verifySuccessful],
                );
            }

            public function verifyWebhookSignature(string $payload, ?string $signature): bool
            {
                return $this->signatureValid;
            }
        };

        $this->app->bind(PaymentProviderManagerInterface::class, fn () => new class($provider) implements PaymentProviderManagerInterface {
            public function __construct(private readonly PaymentProviderContract $provider)
            {
            }

            public function driver(string $provider): PaymentProviderContract
            {
                return $this->provider;
            }
        });
    }

    private function reference(string $prefix): string
    {
        return strtoupper($prefix).'-'.strtoupper((string) Str::ulid());
    }
}
