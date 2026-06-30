<?php

namespace App\Domain\Payment\Enums;

enum PaymentWebhookStatus: string
{
    case RECEIVED = 'received';
    case PROCESSED = 'processed';
    case DUPLICATE = 'duplicate';
    case FAILED = 'failed';
    case INVALID_SIGNATURE = 'invalid_signature';
}
