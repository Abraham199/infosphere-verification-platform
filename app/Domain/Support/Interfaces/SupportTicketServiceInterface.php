<?php

namespace App\Domain\Support\Interfaces;

use App\Domain\Support\DTOs\TicketData;
use App\Domain\Support\Enums\TicketStatus;
use App\Domain\Support\Models\SupportTicket;

interface SupportTicketServiceInterface
{
    public function create(TicketData $data): SupportTicket;
    public function transition(SupportTicket $ticket, TicketStatus $nextStatus, ?string $actorId = null): SupportTicket;
}
