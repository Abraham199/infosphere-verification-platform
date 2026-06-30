<?php

namespace App\Domain\Developer\Enums;

enum WebhookDeliveryStatus: string
{
    case PENDING = 'pending';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';
    case RETRYING = 'retrying';
}
