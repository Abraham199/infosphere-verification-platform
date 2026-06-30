<?php

namespace App\Domain\Payment\Services;

use App\Contracts\Payments\PaymentProviderContract;
use App\Domain\Payment\Enums\PaymentProvider;
use App\Domain\Payment\Exceptions\PaymentException;
use App\Domain\Payment\Interfaces\PaymentProviderManagerInterface;
use App\Infrastructure\Paystack\PaystackAdapter;

class PaymentProviderManager implements PaymentProviderManagerInterface
{
    public function __construct(private readonly PaystackAdapter $paystack)
    {
    }

    public function driver(string $provider): PaymentProviderContract
    {
        return match ($provider) {
            PaymentProvider::PAYSTACK->value => $this->paystack,
            default => throw new PaymentException("Unsupported payment provider [{$provider}]."),
        };
    }
}
