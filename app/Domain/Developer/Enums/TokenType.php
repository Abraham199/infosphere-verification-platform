<?php

namespace App\Domain\Developer\Enums;

enum TokenType: string
{
    case PERSONAL_ACCESS = 'personal_access';
    case SERVICE_TO_SERVICE = 'service_to_service';
    case OAUTH_PLACEHOLDER = 'oauth_placeholder';
}
