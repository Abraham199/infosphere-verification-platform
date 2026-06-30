<?php

namespace App\Domain\Support\Services;

use App\Domain\Support\DTOs\TicketData;
use App\Domain\Support\Enums\TicketStatus;
use App\Domain\Support\Events\TicketClosed;
use App\Domain\Support\Events\TicketCreated;
use App\Domain\Support\Events\TicketResolved;
use App\Domain\Support\Events\TicketUpdated;
use App\Domain\Support\Interfaces\SlaEngineInterface;
use App\Domain\Support\Interfaces\SupportRepositoryInterface;
use App\Domain\Support\Interfaces\SupportTicketServiceInterface;
use App\Domain\Support\Models\SupportTicket;
use App\Domain\Support\Validators\SupportValidationService;
use Illuminate\Support\Str;

class TicketService implements SupportTicketServiceInterface
{
    public function __construct(
        private readonly SupportRepositoryInterface $support,
        private readonly SlaEngineInterface $sla,
        private readonly SupportValidationService $validator,
    ) {
    }

    public function create(TicketData $data): SupportTicket
    {
        $reference = $data->reference ?? 'TCK-'.strtoupper((string) Str::ulid());
        $this->validator->validateTicket($data, $reference);

        $ticket = $this->support->createTicket([
            'tenant_id' => $data->tenantId,
            'requester_id' => $data->requesterId,
            'support_category_id' => $data->categoryId,
            'support_priority_id' => $data->priorityId,
            'ticket_reference' => $reference,
            'subject' => $data->subject,
            'description' => $data->description,
            'status' => TicketStatus::OPEN,
            'source' => $data->source,
            'metadata' => $data->metadata,
        ]);

        $ticket = $this->sla->applyToTicket($ticket);

        event(new TicketCreated($ticket));

        return $ticket;
    }

    public function transition(SupportTicket $ticket, TicketStatus $nextStatus, ?string $actorId = null): SupportTicket
    {
        $this->validator->validateTransition($ticket, $nextStatus);

        $attributes = ['status' => $nextStatus];

        if ($nextStatus === TicketStatus::RESOLVED) {
            $attributes['resolved_at'] = now();
        }

        if ($nextStatus === TicketStatus::CLOSED) {
            $attributes['closed_at'] = now();
        }

        if (in_array($nextStatus, [TicketStatus::ASSIGNED, TicketStatus::IN_PROGRESS], true) && $ticket->first_responded_at === null) {
            $attributes['first_responded_at'] = now();
        }

        $ticket->forceFill($attributes)->save();

        event(match ($nextStatus) {
            TicketStatus::RESOLVED => new TicketResolved($ticket),
            TicketStatus::CLOSED => new TicketClosed($ticket),
            default => new TicketUpdated($ticket),
        });

        return $ticket;
    }
}
