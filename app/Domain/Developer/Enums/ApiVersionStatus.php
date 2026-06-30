<?php

namespace App\Domain\Developer\Enums;

enum ApiVersionStatus: string
{
    case ACTIVE = 'active';
    case DEPRECATED = 'deprecated';
    case SUNSET = 'sunset';
}
