<?php

namespace App\Domain\Wallet\Enums;

enum ReservationStatus: string
{
    case ACTIVE = 'active';
    case RELEASED = 'released';
    case CAPTURED = 'captured';
    case EXPIRED = 'expired';
}
