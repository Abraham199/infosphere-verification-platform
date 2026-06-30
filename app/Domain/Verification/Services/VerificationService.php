<?php

namespace App\Domain\Verification\Services;

use App\Domain\Ledger\Enums\LedgerEntryType;
use App\Domain\Ledger\Interfaces\LedgerPostingInterface;
use App\Domain\Ledger\Interfaces\LedgerRepositoryInterface;
use App\Domain\Ledger\ValueObjects\JournalEntry;
use App\Domain\Ledger\ValueObjects\LedgerLine;
use App\Domain\Verification\DTOs\VerificationRequestData;
use App\Domain\Verification\Enums\VerificationResultStatus;
use App\Domain\Verification\Enums\VerificationStatus;
use App\Domain\Verification\Events\ProviderRequestSent;
use App\Domain\Verification\Events\ProviderResponseReceived;
use App\Domain\Verification\Events\VerificationCompleted;
use App\Domain\Verification\Events\VerificationFailed;
use App\Domain\Verification\Events\VerificationRequested;
use App\Domain\Verification\Events\VerificationSubmitted;
use App\Domain\Verification\Exceptions\VerificationException;
use App\Domain\Verification\Interfaces\VerificationProviderManagerInterface;
use App\Domain\Verification\Interfaces\VerificationRepositoryInterface;
use App\Domain\Verification\Interfaces\VerificationServiceInterface;
use App\Domain\Verification\Models\VerificationProviderLog;
use App\Domain\Verification\Models\VerificationRequest;
use App\Domain\Verification\Models\VerificationResult;
use App\Domain\Verification\Validators\VerificationValidationService;
use App\Domain\Wallet\Interfaces\ReservationInterface;
use App\Domain\Wallet\Interfaces\WalletServiceInterface;
use App\Domain\Wallet\ValueObjects\Currency;
use App\Domain\Wallet\ValueObjects\Money;
use App\Services\BaseService;
use Illuminate\Support\Facades\Hash;

class VerificationService extends BaseService implements VerificationServiceInterface
{
    public function __construct(
        private readonly VerificationRepositoryInterface $verifications,
        private readonly VerificationProviderManagerInterface $providers,
        private readonly VerificationPricingService $pricing,
        private readonly VerificationValidationService $validator,
        private readonly VerificationStateMachine $states,
        private readonly ReservationInterface $reservations,
        private readonly WalletServiceInterface $wallets,
        private readonly LedgerPostingInterface $ledger,
        private readonly LedgerRepositoryInterface $ledgerAccounts,
    ) {
    }

    public function request(VerificationRequestData $data): VerificationRequest
    {
        if ($existing = $this->verifications->findByIdempotencyKey($data->idempotencyKey)) {
            return $existing;
        }

        $this->validator->assertReferenceIsValid($data->reference);
        $this->validator->assertReferenceIsUnique($data->reference);

        $service = $this->verifications->findServiceByCode($data->serviceCode)
            ?? throw new VerificationException('Verification service was not found.');
        $this->validator->assertServiceAvailable($service);
        $price = $this->pricing->resolve($data->tenantId, $service);
        $this->validator->assertPricingAvailable($price);

        $providerService = $this->validator->assertProviderServiceAvailable(
            $service->providerServices()->where('status', 'active')->orderBy('priority')->first()
        );

        return $this->transaction(function () use ($data, $service, $providerService, $price): VerificationRequest {
            $request = VerificationRequest::query()->create([
                'tenant_id' => $data->tenantId,
                'wallet_id' => $data->wallet->id,
                'verification_service_id' => $service->id,
                'provider_service_id' => $providerService->id,
                'provider' => $providerService->provider,
                'reference' => $data->reference,
                'idempotency_key' => $data->idempotencyKey,
                'price_charged' => $price->decimal(),
                'currency' => $price->currency->value(),
                'status' => VerificationStatus::CREATED,
                'subject_identifier_hash' => $data->subjectIdentifier ? Hash::make($data->subjectIdentifier) : null,
                'request_payload' => $this->maskPayload($data->payload),
                'metadata' => $data->metadata,
            ]);

            VerificationRequested::dispatch($request);

            $reservation = $this->reservations->reserve($data->wallet, $price, 'RES-'.$data->reference, metadata: [
                'verification_request_id' => $request->id,
            ]);
            $request->forceFill(['wallet_reservation_id' => $reservation->id])->save();

            $this->states->transition($request, VerificationStatus::VALIDATING);
            $this->states->transition($request, VerificationStatus::SUBMITTED);
            VerificationSubmitted::dispatch($request->refresh());
            ProviderRequestSent::dispatch($request);

            $started = microtime(true);
            $response = $this->providers
                ->driver($providerService->provider->value)
                ->submit($providerService->provider_service_code, $data->payload, $data->reference);

            VerificationProviderLog::query()->create([
                'tenant_id' => $data->tenantId,
                'verification_request_id' => $request->id,
                'provider' => $providerService->provider,
                'request_reference' => $data->reference,
                'response_reference' => $response->providerReference,
                'status' => $response->status,
                'latency_ms' => (int) ((microtime(true) - $started) * 1000),
                'retry_count' => 0,
                'error_message' => $response->errorMessage,
                'request_payload' => $this->maskPayload($data->payload),
                'response_payload' => $this->maskPayload($response->payload),
            ]);
            ProviderResponseReceived::dispatch($request);

            if (! $response->successful) {
                $this->reservations->release($reservation);
                $failed = $this->states->transition($request, VerificationStatus::FAILED, $response->errorMessage);
                VerificationFailed::dispatch($failed);

                return $failed;
            }

            $this->reservations->release($reservation);
            $walletTransaction = $this->wallets->debit($data->wallet, $price, 'VER-'.$data->reference, 'verification-debit-'.$request->id, [
                'verification_request_id' => $request->id,
            ]);
            $ledgerBatch = $this->postLedger($request, $walletTransaction->id, $price);

            $request->forceFill([
                'wallet_transaction_id' => $walletTransaction->id,
                'ledger_batch_id' => $ledgerBatch->id,
                'provider_reference' => $response->providerReference,
            ])->save();

            VerificationResult::query()->create([
                'tenant_id' => $data->tenantId,
                'verification_request_id' => $request->id,
                'result_status' => VerificationResultStatus::tryFrom((string) $response->resultStatus) ?? VerificationResultStatus::MATCH,
                'confidence_score' => $response->confidenceScore,
                'summary' => $response->summary,
                'normalized_data' => $response->normalizedData,
                'provider_payload' => $this->maskPayload($response->payload),
            ]);

            $completed = $this->states->transition($request, VerificationStatus::COMPLETED);
            VerificationCompleted::dispatch($completed);

            return $completed;
        });
    }

    private function postLedger(VerificationRequest $request, string $walletTransactionId, Money $price): \App\Domain\Ledger\Models\LedgerBatch
    {
        $walletLiability = $this->ledgerAccounts->findTenantAccount($request->tenant_id, 'tenant_wallet_liability', $request->currency);
        $revenue = $this->ledgerAccounts->findTenantAccount($request->tenant_id, 'platform_verification_revenue', $request->currency);

        if (! $walletLiability || ! $revenue) {
            throw new VerificationException('Required verification ledger accounts are missing.');
        }

        return $this->ledger->post(new JournalEntry(
            tenantId: $request->tenant_id,
            reference: 'LEDGER-'.$request->reference,
            description: 'Verification charge posting',
            currency: new Currency($request->currency),
            lines: [
                new LedgerLine($walletLiability->id, LedgerEntryType::DEBIT, $price),
                new LedgerLine($revenue->id, LedgerEntryType::CREDIT, $price),
            ],
            metadata: ['verification_request_id' => $request->id, 'wallet_transaction_id' => $walletTransactionId],
            sourceType: $request::class,
            sourceId: $request->id,
        ));
    }

    private function maskPayload(array $payload): array
    {
        foreach (['nin', 'bvn', 'password', 'token', 'secret', 'api_key'] as $key) {
            if (array_key_exists($key, $payload)) {
                $payload[$key] = '***';
            }
        }

        return $payload;
    }
}
