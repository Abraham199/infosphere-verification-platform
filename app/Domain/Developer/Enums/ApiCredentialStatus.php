<?php

namespace App\Domain\Developer\Enums;

enum ApiCredentialStatus: string
{
    case ACTIVE = 'active';
    case REVOKED = 'revoked';
    case EXPIRED = 'expired';
    case SUSPENDED = 'suspended';
}
