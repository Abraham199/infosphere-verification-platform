<?php

namespace App\Domain\Developer\Interfaces;

use App\Domain\Developer\DTOs\ApiRequestContext;

interface ApiGatewayInterface
{
    public function accept(ApiRequestContext $context): ApiRequestContext;
}
