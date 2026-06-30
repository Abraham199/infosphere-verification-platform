<?php

namespace App\Domain\Developer\Services;

use App\Domain\Developer\Enums\ApiVersionStatus;
use App\Domain\Developer\Exceptions\InvalidApiVersionException;
use App\Domain\Developer\Interfaces\ApiVersionResolverInterface;
use App\Domain\Developer\Interfaces\DeveloperRepositoryInterface;
use App\Domain\Developer\Models\ApiVersion;

class ApiVersionResolver implements ApiVersionResolverInterface
{
    public function __construct(private readonly DeveloperRepositoryInterface $developers)
    {
    }

    public function resolve(?string $version): ApiVersion
    {
        $apiVersion = $version === null ? $this->developers->defaultVersion() : $this->developers->findVersion($version);

        if ($apiVersion === null || $apiVersion->status === ApiVersionStatus::SUNSET) {
            throw new InvalidApiVersionException('Invalid or sunset API version.');
        }

        return $apiVersion;
    }
}
