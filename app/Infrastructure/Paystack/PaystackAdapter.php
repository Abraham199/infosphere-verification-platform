<?php

namespace App\Infrastructure\Paystack;

use App\Contracts\Payments\PaymentProviderContract;
use App\Domain\Payment\DTOs\PaymentProviderResponse;
use Illuminate\Support\Facades\Http;

class PaystackAdapter implements PaymentProviderContract
{
    public function initialize(array $payload): PaymentProviderResponse
    {
        $response = Http::withToken((string) config('services.paystack.secret_key'))
            ->acceptJson()
            ->post($this->url('/transaction/initialize'), $payload);

        $body = $response->json() ?? [];
        $data = $body['data'] ?? [];

        return new PaymentProviderResponse(
            successful: $response->successful() && ($body['status'] ?? false) === true,
            status: ($body['status'] ?? false) === true ? 'initialized' : 'failed',
            providerReference: $data['reference'] ?? null,
            authorizationUrl: $data['authorization_url'] ?? null,
            failureReason: $body['message'] ?? null,
            payload: $body,
        );
    }

    public function verify(string $reference): PaymentProviderResponse
    {
        $response = Http::withToken((string) config('services.paystack.secret_key'))
            ->acceptJson()
            ->get($this->url('/transaction/verify/'.rawurlencode($reference)));

        $body = $response->json() ?? [];
        $data = $body['data'] ?? [];
        $status = (string) ($data['status'] ?? 'failed');

        return new PaymentProviderResponse(
            successful: $response->successful() && ($body['status'] ?? false) === true && $status === 'success',
            status: $status === 'success' ? 'success' : 'failed',
            providerReference: $data['reference'] ?? $reference,
            paymentMethod: $data['channel'] ?? null,
            paidAt: $data['paid_at'] ?? null,
            failureReason: $status === 'success' ? null : ($data['gateway_response'] ?? $body['message'] ?? 'Payment verification failed.'),
            payload: $body,
        );
    }

    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        if ($signature === null || $signature === '') {
            return false;
        }

        $secret = (string) config('services.paystack.secret_key');

        if ($secret === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha512', $payload, $secret), $signature);
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.paystack.base_url', 'https://api.paystack.co'), '/').$path;
    }
}
