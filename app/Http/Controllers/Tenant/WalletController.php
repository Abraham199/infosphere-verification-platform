<?php

namespace App\Http\Controllers\Tenant;

use App\Domain\Payment\DTOs\PaymentInitializationData;
use App\Domain\Payment\Interfaces\PaymentServiceInterface;
use App\Domain\Payment\Models\PaymentTransaction;
use App\Domain\Tenancy\Services\TenantContext;
use App\Domain\Wallet\Models\Wallet;
use App\Domain\Wallet\ValueObjects\Money;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\InitializeWalletFundingRequest;
use App\Http\ViewModels\ExperienceDashboardData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class WalletController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly ExperienceDashboardData $data,
        private readonly PaymentServiceInterface $payments,
    ) {
    }

    public function overview(): View
    {
        $tenant = $this->tenantContext->require();

        return view('tenant.wallet.index', [
            'tenant' => $tenant,
            ...$this->data->wallet($tenant),
        ]);
    }

    public function transactions(): View
    {
        $tenant = $this->tenantContext->require();

        return view('tenant.wallet.transactions', [
            'tenant' => $tenant,
            ...$this->data->wallet($tenant),
        ]);
    }

    public function funding(): View
    {
        $tenant = $this->tenantContext->require();

        return view('tenant.wallet.funding', [
            'tenant' => $tenant,
            'wallet' => $this->tenantWallet($tenant->id),
            ...$this->data->wallet($tenant),
        ]);
    }

    public function initializeFunding(InitializeWalletFundingRequest $request): RedirectResponse
    {
        $tenant = $this->tenantContext->require();
        $wallet = $this->tenantWallet($tenant->id);

        if ($wallet === null) {
            return back()
                ->withInput()
                ->withErrors(['wallet' => 'A tenant wallet must exist before funding can be initialized.']);
        }

        $reference = 'PAY-'.strtoupper((string) Str::ulid());

        try {
            $payment = $this->payments->initialize(new PaymentInitializationData(
                tenantId: $tenant->id,
                wallet: $wallet,
                amount: Money::fromDecimal($request->validated('amount'), $request->validated('currency')),
                email: $request->validated('email'),
                reference: $reference,
                callbackUrl: route('tenant.wallet.funding.show', ['tenant' => $tenant, 'reference' => $reference]),
                idempotencyKey: 'tenant-wallet-funding-'.$tenant->id.'-'.$reference,
                metadata: [
                    'initiated_by' => $request->user()?->id,
                    'source' => 'tenant_wallet_ui',
                ],
            ));
        } catch (Throwable $exception) {
            Log::warning('Wallet funding initialization failed.', [
                'tenant_id' => $tenant->id,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['payment' => 'Payment initialization failed. Please try again or contact support.']);
        }

        return redirect()
            ->route('tenant.wallet.funding.show', ['tenant' => $tenant, 'reference' => $payment->reference])
            ->with('status', 'Wallet funding was initialized.');
    }

    public function fundingStatus(string $tenantSlug, string $reference): View
    {
        $tenant = $this->tenantContext->require();
        $payment = PaymentTransaction::query()
            ->where('tenant_id', $tenant->id)
            ->where('reference', $reference)
            ->firstOrFail();

        return view('tenant.wallet.funding-status', [
            'tenant' => $tenant,
            'payment' => $payment,
        ]);
    }

    public function reservations(): View
    {
        $tenant = $this->tenantContext->require();

        return view('tenant.wallet.reservations', [
            'tenant' => $tenant,
            ...$this->data->wallet($tenant),
        ]);
    }

    private function tenantWallet(string $tenantId): ?Wallet
    {
        return Wallet::query()
            ->where('tenant_id', $tenantId)
            ->where('currency', 'NGN')
            ->first();
    }
}
