<?php

namespace App\Domain\Product\Enums;

enum ProviderMappingStatus: string
{
    case ACTIVE = 'active';
    case DISABLED = 'disabled';
}
