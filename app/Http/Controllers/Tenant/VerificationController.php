<?php

namespace App\Http\Controllers\Tenant;

use App\Domain\Tenancy\Services\TenantContext;
use App\Domain\Verification\DTOs\VerificationRequestData;
use App\Domain\Verification\Exceptions\VerificationException;
use App\Domain\Verification\Interfaces\VerificationServiceInterface;
use App\Domain\Verification\Models\VerificationRequest;
use App\Domain\Verification\Models\VerificationService as VerificationCatalogService;
use App\Domain\Verification\Services\VerificationPricingService;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\ValueObjects\Money;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\SubmitVerificationRequest;
use App\Http\ViewModels\ExperienceDashboardData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class VerificationController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly ExperienceDashboardData $data,
        private readonly VerificationPricingService $pricing,
        private readonly VerificationServiceInterface $verifications,
    ) {
    }

    public function index(Request $request): View
    {
        $tenant = $this->tenantContext->require();

        return view('tenant.verification.index', [
            'tenant' => $tenant,
            'selectedService' => $this->selectedService($request->query('service')),
            'wallet' => $this->tenantWallet($tenant->id),
            ...$this->data->verification($tenant),
        ]);
    }

    public function services(): View
    {
        $tenant = $this->tenantContext->require();

        return view('tenant.verification.services', [
            'tenant' => $tenant,
            'services' => $this->pricedServices($tenant->id),
        ]);
    }

    public function submit(SubmitVerificationRequest $request): RedirectResponse
    {
        $tenant = $this->tenantContext->require();
        $service = VerificationCatalogService::query()
            ->where('service_code', $request->validated('service_code'))
            ->where('status', 'active')
            ->firstOrFail();
        $wallet = $this->tenantWallet($tenant->id);

        if ($wallet === null) {
            return back()->withInput()->withErrors(['wallet' => 'A tenant wallet is required before verification can be submitted.']);
        }

        $price = $this->pricing->resolve($tenant->id, $service);
        $available = Money::fromDecimal($wallet->available_balance, $wallet->currency);

        if ($price->greaterThan($available)) {
            return back()
                ->withInput()
                ->withErrors(['wallet' => 'Wallet balance is insufficient for this verification request.']);
        }

        $reference = 'VER-'.strtoupper((string) Str::ulid());

        try {
            $verification = $this->verifications->request(new VerificationRequestData(
                tenantId: $tenant->id,
                wallet: $wallet,
                serviceCode: $service->service_code,
                reference: $reference,
                payload: $request->payload(),
                subjectIdentifier: $request->validated('subject_identifier'),
                idempotencyKey: 'tenant-verification-'.$tenant->id.'-'.$reference,
                metadata: [
                    'initiated_by' => $request->user()?->id,
                    'source' => 'tenant_verification_ui',
                ],
            ));
        } catch (VerificationException $exception) {
            return back()->withInput()->withErrors(['verification' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            Log::warning('Verification submission failed.', [
                'tenant_id' => $tenant->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->withInput()->withErrors(['verification' => 'Verification could not be submitted. Please try again or contact support.']);
        }

        return redirect()
            ->route('tenant.verification.show', ['tenant' => $tenant, 'reference' => $verification->reference])
            ->with('status', 'Verification request submitted successfully.');
    }

    public function history(Request $request): View
    {
        $tenant = $this->tenantContext->require();
        $search = trim((string) $request->query('search'));
        $status = trim((string) $request->query('status'));

        $requests = VerificationRequest::query()
            ->with('service')
            ->where('tenant_id', $tenant->id)
            ->when($search !== '', fn ($query) => $query->where('reference', 'like', "%{$search}%"))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->limit(50)
            ->get();

        return view('tenant.verification.history', [
            'tenant' => $tenant,
            'requests' => $requests,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function show(string $tenantSlug, string $reference): View
    {
        $tenant = $this->tenantContext->require();
        $verification = VerificationRequest::query()
            ->with(['service', 'reservation', 'walletTransaction', 'result'])
            ->where('tenant_id', $tenant->id)
            ->where('reference', $reference)
            ->firstOrFail();

        return view('tenant.verification.show', [
            'tenant' => $tenant,
            'verification' => $verification,
        ]);
    }

    public function result(string $tenantSlug, string $reference): View
    {
        $tenant = $this->tenantContext->require();
        $verification = VerificationRequest::query()
            ->with(['service', 'result'])
            ->where('tenant_id', $tenant->id)
            ->where('reference', $reference)
            ->firstOrFail();

        return view('tenant.verification.result', [
            'tenant' => $tenant,
            'verification' => $verification,
            'result' => $verification->result,
        ]);
    }

    private function selectedService(?string $serviceCode): ?array
    {
        if ($serviceCode === null) {
            return null;
        }

        $tenant = $this->tenantContext->require();
        $service = VerificationCatalogService::query()
            ->where('service_code', $serviceCode)
            ->where('status', 'active')
            ->first();

        if (! $service) {
            return null;
        }

        $price = $this->pricing->resolve($tenant->id, $service);

        return [
            'name' => $service->name,
            'service_code' => $service->service_code,
            'price' => $price->decimal(),
            'currency' => $price->currency->value(),
        ];
    }

    private function pricedServices(string $tenantId): array
    {
        return VerificationCatalogService::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function (VerificationCatalogService $service) use ($tenantId): array {
                $price = $this->pricing->resolve($tenantId, $service);

                return [
                    'name' => $service->name,
                    'service_code' => $service->service_code,
                    'description' => $service->description,
                    'price' => $price->decimal(),
                    'currency' => $price->currency->value(),
                    'status' => $service->status->value,
                ];
            })
            ->all();
    }

    private function tenantWallet(string $tenantId): ?Wallet
    {
        return Wallet::query()
            ->where('tenant_id', $tenantId)
            ->where('currency', 'NGN')
            ->first();
    }
}
