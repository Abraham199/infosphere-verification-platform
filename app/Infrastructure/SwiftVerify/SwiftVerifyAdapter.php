<?php

namespace App\Infrastructure\SwiftVerify;

use App\Contracts\Verification\VerificationProviderContract;
use App\Domain\Verification\DTOs\VerificationProviderResponse;
use Illuminate\Support\Facades\Http;

class SwiftVerifyAdapter implements VerificationProviderContract
{
    public function supports(string $providerServiceCode): bool
    {
        return $providerServiceCode !== '';
    }

    public function submit(string $providerServiceCode, array $payload, string $reference): VerificationProviderResponse
    {
        $started = microtime(true);
        $response = Http::withToken((string) config('services.swiftverify.api_key'))
            ->timeout((int) config('services.swiftverify.timeout', 30))
            ->acceptJson()
            ->post($this->url('/verify/'.$providerServiceCode), [
                'reference' => $reference,
                'data' => $payload,
            ]);

        $body = $response->json() ?? [];
        $normalized = $this->normalize($body);

        return new VerificationProviderResponse(
            successful: $response->successful() && $normalized->successful,
            status: $normalized->status,
            providerReference: $normalized->providerReference,
            resultStatus: $normalized->resultStatus,
            confidenceScore: $normalized->confidenceScore,
            summary: $normalized->summary,
            normalizedData: array_merge($normalized->normalizedData, ['latency_ms' => (int) ((microtime(true) - $started) * 1000)]),
            payload: $body,
            errorMessage: $normalized->errorMessage,
        );
    }

    public function normalize(array $providerResponse): VerificationProviderResponse
    {
        $data = $providerResponse['data'] ?? [];
        $status = strtolower((string) ($data['status'] ?? $providerResponse['status'] ?? 'failed'));
        $successful = in_array($status, ['success', 'completed', 'match', 'verified'], true);

        return new VerificationProviderResponse(
            successful: $successful,
            status: $successful ? 'completed' : 'failed',
            providerReference: $data['reference'] ?? $providerResponse['reference'] ?? null,
            resultStatus: $data['result_status'] ?? ($successful ? 'match' : 'error'),
            confidenceScore: isset($data['confidence_score']) ? (float) $data['confidence_score'] : null,
            summary: $data['summary'] ?? $providerResponse['message'] ?? null,
            normalizedData: $data['normalized'] ?? [],
            payload: $providerResponse,
            errorMessage: $successful ? null : ($providerResponse['message'] ?? 'SwiftVerify verification failed.'),
        );
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.swiftverify.base_url'), '/').$path;
    }
}
