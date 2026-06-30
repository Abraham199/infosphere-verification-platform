<?php

namespace App\Domain\Payment\Services;

use App\Domain\Ledger\Models\LedgerAccount;
use App\Domain\Ledger\Exceptions\MissingLedgerAccountException;
use App\Domain\Payment\DTOs\PaymentInitializationData;
use App\Domain\Payment\DTOs\PaymentProviderResponse;
use App\Domain\Payment\DTOs\PaymentWebhookData;
use App\Domain\Payment\Enums\PaymentAttemptStatus;
use App\Domain\Payment\Enums\PaymentMethod;
use App\Domain\Payment\Enums\PaymentProvider;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Enums\PaymentWebhookStatus;
use App\Domain\Payment\Events\PaymentFailed;
use App\Domain\Payment\Events\PaymentInitialized;
use App\Domain\Payment\Events\PaymentSucceeded;
use App\Domain\Payment\Events\PaymentWalletCredited;
use App\Domain\Payment\Events\PaymentWebhookProcessed;
use App\Domain\Payment\Exceptions\InvalidWebhookSignatureException;
use App\Domain\Payment\Exceptions\PaymentVerificationException;
use App\Domain\Payment\Interfaces\PaymentProviderManagerInterface;
use App\Domain\Payment\Interfaces\PaymentRepositoryInterface;
use App\Domain\Payment\Interfaces\PaymentServiceInterface;
use App\Domain\Payment\Models\PaymentAttempt;
use App\Domain\Payment\Models\PaymentProviderLog;
use App\Domain\Payment\Models\PaymentTransaction;
use App\Domain\Payment\Models\PaymentWebhook;
use App\Domain\Payment\Validators\PaymentValidationService;
use App\Domain\Wallet\Interfaces\WalletLedgerPostingInterface;
use App\Domain\Wallet\Services\WalletCreditService;
use App\Domain\Wallet\ValueObjects\Money;
use App\Services\BaseService;
use Illuminate\Support\Carbon;

class PaymentService extends BaseService implements PaymentServiceInterface
{
    public function __construct(
        private readonly PaymentRepositoryInterface $payments,
        private readonly PaymentProviderManagerInterface $providers,
        private readonly PaymentValidationService $validator,
        private readonly PaymentStateMachine $states,
        private readonly WalletCreditService $walletCredits,
        private readonly WalletLedgerPostingInterface $walletLedger,
    ) {
    }

    public function initialize(PaymentInitializationData $data): PaymentTransaction
    {
        if ($existing = $this->payments->findByIdempotencyKey($data->idempotencyKey)) {
            return $existing;
        }

        $this->validator->assertReferenceIsValid($data->reference);
        $this->validator->assertReferenceIsUnique($data->reference);
        $this->validator->assertPositiveAmount($data->amount);

        return $this->transaction(function () use ($data): PaymentTransaction {
            $payment = PaymentTransaction::query()->create([
                'tenant_id' => $data->tenantId,
                'wallet_id' => $data->wallet->id,
                'provider' => PaymentProvider::PAYSTACK,
                'reference' => $data->reference,
                'idempotency_key' => $data->idempotencyKey,
                'amount' => $data->amount->decimal(),
                'currency' => $data->amount->currency->value(),
                'status' => PaymentStatus::INITIALIZED,
                'initialized_at' => now(),
                'metadata' => $data->metadata,
            ]);

            $payload = [
                'email' => $data->email,
                'amount' => $data->amount->minorUnits,
                'currency' => $data->amount->currency->value(),
                'reference' => $data->reference,
                'callback_url' => $data->callbackUrl,
                'metadata' => array_merge($data->metadata, [
                    'tenant_id' => $data->tenantId,
                    'wallet_id' => $data->wallet->id,
                ]),
            ];

            $response = $this->providers->driver(PaymentProvider::PAYSTACK->value)->initialize(array_filter($payload));

            $this->recordAttempt($payment, $payload, $response);
            $this->recordProviderLog($payment, 'outbound', 'initialize', $response->successful ? 'success' : 'failed', $payload, $response->payload);

            $payment->forceFill([
                'provider_reference' => $response->providerReference,
                'authorization_url' => $response->authorizationUrl,
                'status' => $response->successful ? PaymentStatus::PENDING : PaymentStatus::FAILED,
                'failure_reason' => $response->failureReason,
                'failed_at' => $response->successful ? null : now(),
            ])->save();

            PaymentInitialized::dispatch($payment->refresh());

            if (! $response->successful) {
                PaymentFailed::dispatch($payment);
            }

            return $payment->refresh();
        });
    }

    public function verify(string $reference): PaymentTransaction
    {
        $payment = $this->payments->findByReference($reference)
            ?? throw new PaymentVerificationException('Payment transaction was not found.');

        $response = $this->providers->driver($payment->provider->value)->verify($reference);
        $this->recordProviderLog($payment, 'outbound', 'verify', $response->successful ? 'success' : 'failed', ['reference' => $reference], $response->payload);

        return $this->finalizeVerifiedPayment($payment, $response);
    }

    public function handleWebhook(PaymentWebhookData $webhook): PaymentTransaction
    {
        $provider = $this->providers->driver($webhook->provider);
        $signatureHash = $webhook->signature ? hash('sha256', $webhook->signature) : null;

        if (! $provider->verifyWebhookSignature($webhook->rawPayload, $webhook->signature)) {
            PaymentWebhook::query()->create([
                'provider' => $webhook->provider,
                'event_type' => $webhook->eventType,
                'event_reference' => $webhook->eventReference,
                'signature_hash' => $signatureHash,
                'status' => PaymentWebhookStatus::INVALID_SIGNATURE,
                'received_at' => now(),
                'payload' => $webhook->payload,
                'failure_reason' => 'Invalid webhook signature.',
            ]);

            throw new InvalidWebhookSignatureException('Invalid payment webhook signature.');
        }

        $this->validator->assertWebhookIsNotReplay($webhook->eventReference, $signatureHash);

        $payment = $this->payments->findByReference((string) $webhook->transactionReference)
            ?? throw new PaymentVerificationException('Webhook payment transaction was not found.');

        $storedWebhook = PaymentWebhook::query()->create([
            'tenant_id' => $payment->tenant_id,
            'payment_transaction_id' => $payment->id,
            'provider' => $webhook->provider,
            'event_type' => $webhook->eventType,
            'event_reference' => $webhook->eventReference,
            'signature_hash' => $signatureHash,
            'status' => PaymentWebhookStatus::RECEIVED,
            'received_at' => now(),
            'payload' => $webhook->payload,
        ]);

        $verified = $this->verify($payment->reference);

        $storedWebhook->forceFill([
            'status' => PaymentWebhookStatus::PROCESSED,
            'processed_at' => now(),
        ])->save();

        PaymentWebhookProcessed::dispatch($storedWebhook);

        return $verified;
    }

    private function finalizeVerifiedPayment(PaymentTransaction $payment, PaymentProviderResponse $response): PaymentTransaction
    {
        return $this->transaction(function () use ($payment, $response): PaymentTransaction {
            $lockedPayment = $this->payments->findLocked($payment->id);

            if ($lockedPayment->status === PaymentStatus::SUCCESS) {
                return $lockedPayment;
            }

            if (! $response->successful) {
                $failed = $this->states->transition($lockedPayment, PaymentStatus::FAILED, $response->failureReason);
                PaymentFailed::dispatch($failed);

                return $failed;
            }

            $lockedPayment->forceFill([
                'provider_reference' => $response->providerReference ?? $lockedPayment->provider_reference,
                'payment_method' => $this->normalizePaymentMethod($response->paymentMethod),
                'paid_at' => $response->paidAt ? Carbon::parse($response->paidAt) : now(),
            ])->save();

            $successful = $this->states->transition($lockedPayment, PaymentStatus::SUCCESS);
            $successful->load('wallet');
            $walletTransaction = $this->walletCredits->credit(
                wallet: $successful->wallet,
                amount: Money::fromDecimal($successful->amount, $successful->currency),
                reference: 'WALLET-'.$successful->reference,
                idempotencyKey: 'payment-credit-'.$successful->id,
                metadata: ['payment_transaction_id' => $successful->id],
            );

            $ledgerBatch = $this->postLedger($successful, $walletTransaction);

            $successful->forceFill([
                'wallet_transaction_id' => $walletTransaction->id,
                'ledger_batch_id' => $ledgerBatch?->id,
            ])->save();

            PaymentWalletCredited::dispatch($successful, $walletTransaction);
            PaymentSucceeded::dispatch($successful);

            return $successful->refresh();
        });
    }

    private function postLedger(PaymentTransaction $payment, \App\Domain\Wallet\Models\WalletTransaction $walletTransaction): \App\Domain\Ledger\Models\LedgerBatch
    {
        $walletLiability = LedgerAccount::query()
            ->where('tenant_id', $payment->tenant_id)
            ->where('code', 'tenant_wallet_liability')
            ->where('currency', $payment->currency)
            ->first();

        $cashClearing = LedgerAccount::query()
            ->where('tenant_id', $payment->tenant_id)
            ->where('code', 'cash_clearing')
            ->where('currency', $payment->currency)
            ->first();

        if (! $walletLiability || ! $cashClearing) {
            throw new MissingLedgerAccountException('Required payment ledger accounts are missing.');
        }

        return $this->walletLedger->postWalletCredit($walletTransaction, $walletLiability->id, $cashClearing->id);
    }

    private function recordAttempt(PaymentTransaction $payment, array $payload, PaymentProviderResponse $response): void
    {
        PaymentAttempt::query()->create([
            'tenant_id' => $payment->tenant_id,
            'payment_transaction_id' => $payment->id,
            'provider' => $payment->provider,
            'provider_reference' => $response->providerReference,
            'status' => $response->successful ? PaymentAttemptStatus::SUCCESS : PaymentAttemptStatus::FAILED,
            'attempt_number' => $payment->attempts()->count() + 1,
            'request_payload' => $payload,
            'response_payload' => $response->payload,
            'attempted_at' => now(),
        ]);
    }

    private function recordProviderLog(PaymentTransaction $payment, string $direction, string $action, string $status, array $request, array $response): void
    {
        PaymentProviderLog::query()->create([
            'tenant_id' => $payment->tenant_id,
            'payment_transaction_id' => $payment->id,
            'provider' => $payment->provider,
            'direction' => $direction,
            'action' => $action,
            'status' => $status,
            'request_payload' => $request,
            'response_payload' => $response,
        ]);
    }

    private function normalizePaymentMethod(?string $method): PaymentMethod
    {
        return PaymentMethod::tryFrom((string) $method) ?? PaymentMethod::UNKNOWN;
    }
}
