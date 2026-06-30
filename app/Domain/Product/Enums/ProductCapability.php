<?php

namespace App\Domain\Product\Enums;

enum ProductCapability: string
{
    case REQUIRES_WALLET = 'requires_wallet';
    case REQUIRES_RESERVATION = 'requires_reservation';
    case SUPPORTS_REFUND = 'supports_refund';
    case SUPPORTS_BATCH_PROCESSING = 'supports_batch_processing';
    case SUPPORTS_ASYNC_PROCESSING = 'supports_async_processing';
    case REQUIRES_PROVIDER = 'requires_provider';
    case REQUIRES_PAYMENT_FIRST = 'requires_payment_first';
}
