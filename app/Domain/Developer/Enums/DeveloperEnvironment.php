<?php

namespace App\Domain\Developer\Enums;

enum DeveloperEnvironment: string
{
    case PRODUCTION = 'production';
    case SANDBOX = 'sandbox';
}
