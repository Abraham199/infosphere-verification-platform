<?php

namespace App\Domain\Support\Enums;

enum TicketStatus: string
{
    case OPEN = 'open';
    case ASSIGNED = 'assigned';
    case IN_PROGRESS = 'in_progress';
    case WAITING_FOR_CUSTOMER = 'waiting_for_customer';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';
    case REOPENED = 'reopened';
    case CANCELLED = 'cancelled';

    /**
     * @return array<int, self>
     */
    public function allowedNextStatuses(): array
    {
        return match ($this) {
            self::OPEN => [self::ASSIGNED, self::IN_PROGRESS, self::CANCELLED],
            self::ASSIGNED => [self::IN_PROGRESS, self::WAITING_FOR_CUSTOMER, self::RESOLVED, self::CANCELLED],
            self::IN_PROGRESS => [self::WAITING_FOR_CUSTOMER, self::RESOLVED, self::CANCELLED],
            self::WAITING_FOR_CUSTOMER => [self::IN_PROGRESS, self::RESOLVED, self::CANCELLED],
            self::RESOLVED => [self::CLOSED, self::REOPENED],
            self::CLOSED => [self::REOPENED],
            self::REOPENED => [self::ASSIGNED, self::IN_PROGRESS, self::CANCELLED],
            self::CANCELLED => [],
        };
    }
}
