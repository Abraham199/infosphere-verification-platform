<?php

namespace App\Domain\Payment\Enums;

enum PaymentAttemptStatus: string
{
    case STARTED = 'started';
    case SUCCESS = 'success';
    case FAILED = 'failed';
}
