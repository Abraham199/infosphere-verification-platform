<?php

namespace App\Domain\Product\Enums;

enum ProductStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case DISABLED = 'disabled';
    case MAINTENANCE = 'maintenance';
    case DEPRECATED = 'deprecated';
    case ARCHIVED = 'archived';
}
