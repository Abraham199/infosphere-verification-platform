<?php

namespace App\Domain\Support\Services;

use App\Domain\Support\DTOs\AssignmentData;
use App\Domain\Support\Enums\AssignmentType;
use App\Domain\Support\Enums\TicketStatus;
use App\Domain\Support\Events\TicketAssigned;
use App\Domain\Support\Events\TicketEscalated;
use App\Domain\Support\Exceptions\InvalidSupportConfigurationException;
use App\Domain\Support\Interfaces\AssignmentEngineInterface;
use App\Domain\Support\Interfaces\SupportRepositoryInterface;
use App\Domain\Support\Models\SupportAssignment;

class AssignmentEngine implements AssignmentEngineInterface
{
    public function __construct(private readonly SupportRepositoryInterface $support)
    {
    }

    public function assign(AssignmentData $data): SupportAssignment
    {
        $ticket = $this->support->findTicket($data->ticketId);

        if ($ticket === null) {
            throw new InvalidSupportConfigurationException('Support ticket not found for assignment.');
        }

        if ($data->assignedToUserId === null && $data->assignedTeam === null) {
            throw new InvalidSupportConfigurationException('Assignment requires a user or team.');
        }

        $assignment = $this->support->createAssignment([
            'support_ticket_id' => $data->ticketId,
            'assigned_to_user_id' => $data->assignedToUserId,
            'assigned_team' => $data->assignedTeam,
            'assignment_type' => $data->assignmentType,
            'assigned_by' => $data->assignedBy,
            'assigned_at' => now(),
            'metadata' => $data->metadata,
        ]);

        if ($ticket->status === TicketStatus::OPEN || $ticket->status === TicketStatus::REOPENED) {
            $ticket->forceFill(['status' => TicketStatus::ASSIGNED])->save();
        }

        event($data->assignmentType === AssignmentType::ESCALATION ? new TicketEscalated($ticket) : new TicketAssigned($assignment));

        return $assignment;
    }
}
