<?php

namespace App\Domain\Support\Events;

use App\Domain\Support\Models\SupportTicket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketResolved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly SupportTicket $ticket)
    {
    }
}
