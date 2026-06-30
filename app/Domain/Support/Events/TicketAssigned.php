<?php

namespace App\Domain\Support\Events;

use App\Domain\Support\Models\SupportAssignment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketAssigned
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly SupportAssignment $assignment)
    {
    }
}
