<?php

namespace App\Domain\Verification\Enums;

enum VerificationResultStatus: string
{
    case MATCH = 'match';
    case NO_MATCH = 'no_match';
    case PARTIAL_MATCH = 'partial_match';
    case UNAVAILABLE = 'unavailable';
    case ERROR = 'error';
}
