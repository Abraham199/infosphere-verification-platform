<?php

namespace App\Domain\Support\Validators;

use App\Domain\Support\DTOs\SlaPolicyData;
use App\Domain\Support\DTOs\TicketData;
use App\Domain\Support\Enums\TicketStatus;
use App\Domain\Support\Exceptions\DuplicateTicketReferenceException;
use App\Domain\Support\Exceptions\InvalidSupportConfigurationException;
use App\Domain\Support\Exceptions\InvalidTicketStatusTransitionException;
use App\Domain\Support\Interfaces\SupportRepositoryInterface;
use App\Domain\Support\Models\SupportTicket;

class SupportValidationService
{
    public function __construct(private readonly SupportRepositoryInterface $support)
    {
    }

    public function validateTicket(TicketData $data, string $reference): void
    {
        if (trim($data->subject) === '' || trim($data->description) === '') {
            throw new InvalidSupportConfigurationException('Support ticket subject and description are required.');
        }

        if ($this->support->ticketReferenceExists($reference)) {
            throw new DuplicateTicketReferenceException('Duplicate support ticket reference.');
        }

        if ($data->categoryId !== null && $this->support->findCategory($data->tenantId, $data->categoryId) === null) {
            throw new InvalidSupportConfigurationException('Invalid support category.');
        }

        if ($data->priorityId !== null && $this->support->findPriority($data->tenantId, $data->priorityId) === null) {
            throw new InvalidSupportConfigurationException('Invalid support priority.');
        }
    }

    public function validateTransition(SupportTicket $ticket, TicketStatus $nextStatus): void
    {
        if (! in_array($nextStatus, $ticket->status->allowedNextStatuses(), true)) {
            throw new InvalidTicketStatusTransitionException("Cannot transition ticket from [{$ticket->status->value}] to [{$nextStatus->value}].");
        }
    }

    public function validateSlaPolicy(SlaPolicyData $data): void
    {
        if ($data->firstResponseMinutes <= 0 || $data->resolutionMinutes <= 0) {
            throw new InvalidSupportConfigurationException('SLA response and resolution targets must be positive.');
        }

        if ($data->escalationMinutes !== null && $data->escalationMinutes > $data->resolutionMinutes) {
            throw new InvalidSupportConfigurationException('SLA escalation target must be before resolution target.');
        }
    }
}
