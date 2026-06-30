<?php

namespace App\Domain\Verification\Enums;

enum VerificationServiceStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
