<?php

namespace App\Domain\Product\Enums;

enum ProductCategoryStatus: string
{
    case ACTIVE = 'active';
    case DISABLED = 'disabled';
    case ARCHIVED = 'archived';
}
