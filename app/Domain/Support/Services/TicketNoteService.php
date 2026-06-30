<?php

namespace App\Domain\Support\Services;

use App\Domain\Support\DTOs\TicketNoteData;
use App\Domain\Support\Exceptions\InvalidSupportConfigurationException;
use App\Domain\Support\Interfaces\SupportRepositoryInterface;
use App\Domain\Support\Models\SupportTicketNote;

class TicketNoteService
{
    public function __construct(private readonly SupportRepositoryInterface $support)
    {
    }

    public function create(TicketNoteData $data): SupportTicketNote
    {
        if ($this->support->findTicket($data->ticketId) === null) {
            throw new InvalidSupportConfigurationException('Support ticket not found for note.');
        }

        if (trim($data->body) === '') {
            throw new InvalidSupportConfigurationException('Support ticket note body is required.');
        }

        return $this->support->createNote([
            'support_ticket_id' => $data->ticketId,
            'author_id' => $data->authorId,
            'body' => $data->body,
            'is_internal' => $data->internal,
            'metadata' => $data->metadata,
        ]);
    }
}
