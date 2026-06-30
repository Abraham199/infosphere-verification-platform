<?php

namespace App\Domain\Product\Enums;

enum ProductVisibility: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case TENANT_ONLY = 'tenant_only';
}
