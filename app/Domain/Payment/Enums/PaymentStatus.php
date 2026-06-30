<?php

namespace App\Domain\Payment\Enums;

enum PaymentStatus: string
{
    case INITIALIZED = 'initialized';
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case ABANDONED = 'abandoned';
    case CANCELLED = 'cancelled';
    case REVERSED = 'reversed';
}
