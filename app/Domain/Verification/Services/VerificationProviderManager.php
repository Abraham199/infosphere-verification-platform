<?php

namespace App\Domain\Verification\Services;

use App\Contracts\Verification\VerificationProviderContract;
use App\Domain\Verification\Enums\VerificationProvider;
use App\Domain\Verification\Exceptions\VerificationProviderException;
use App\Domain\Verification\Interfaces\VerificationProviderManagerInterface;
use App\Infrastructure\SwiftVerify\SwiftVerifyAdapter;

class VerificationProviderManager implements VerificationProviderManagerInterface
{
    public function __construct(private readonly SwiftVerifyAdapter $swiftVerify)
    {
    }

    public function driver(string $provider): VerificationProviderContract
    {
        return match ($provider) {
            VerificationProvider::SWIFTVERIFY->value => $this->swiftVerify,
            default => throw new VerificationProviderException("Unsupported verification provider [{$provider}]."),
        };
    }
}
