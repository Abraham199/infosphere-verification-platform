<?php

namespace App\Domain\Verification\Enums;

enum VerificationStatus: string
{
    case CREATED = 'created';
    case VALIDATING = 'validating';
    case SUBMITTED = 'submitted';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
}
