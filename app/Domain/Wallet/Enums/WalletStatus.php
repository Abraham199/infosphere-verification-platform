<?php

namespace App\Domain\Wallet\Enums;

enum WalletStatus: string
{
    case ACTIVE = 'active';
    case FROZEN = 'frozen';
    case SUSPENDED = 'suspended';
    case CLOSED = 'closed';
}
