<?php

namespace App\Domain\Payment\Interfaces;

use App\Contracts\Payments\PaymentProviderContract;

interface PaymentProviderManagerInterface
{
    public function driver(string $provider): PaymentProviderContract;
}
