<?php

namespace App\Domain\Developer\Interfaces;

use App\Domain\Developer\Models\ApiVersion;

interface ApiVersionResolverInterface
{
    public function resolve(?string $version): ApiVersion;
}
