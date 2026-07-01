<?php

namespace App\Http\Controllers\Platform;

use App\Domain\Support\Enums\AssignmentType;
use App\Domain\Support\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\AssignSupportTicketRequest;
use App\Http\Requests\Platform\StoreSupportTicketInternalNoteRequest;
use App\Http\Requests\Platform\UpdateSupportTicketStatusRequest;
use App\Services\SupportDeskWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function __construct(private readonly SupportDeskWorkflowService $support)
    {
    }

    public function index(Request $request): View
    {
        $status = $request->query('status');

        return view('platform.support.index', [
            'tickets' => $this->support->platformTickets(is_string($status) ? $status : null),
            'status' => $status,
            'statuses' => TicketStatus::cases(),
        ]);
    }

    public function show(string $reference): View
    {
        $ticket = $this->support->platformTicket($reference);
        $isBreached = $this->support->markSlaState($ticket);

        return view('platform.support.show', [
            'ticket' => $ticket->refresh()->load(['tenant', 'requester', 'category', 'priority', 'slaPolicy', 'assignments.assignee', 'notes.author', 'attachments']),
            'isBreached' => $isBreached,
            'statuses' => $ticket->status->allowedNextStatuses(),
            'assignmentTypes' => AssignmentType::cases(),
        ]);
    }

    public function assign(AssignSupportTicketRequest $request, string $reference): RedirectResponse
    {
        $ticket = $this->support->platformTicket($reference);

        $this->support->assign(
            ticket: $ticket,
            type: AssignmentType::from($request->validated('assignment_type')),
            team: $request->validated('assigned_team'),
            userId: $request->validated('assigned_to_user_id'),
            assignedBy: $request->user()->id,
        );

        return redirect()
            ->route('platform.support.show', ['reference' => $ticket->ticket_reference])
            ->with('status', 'Ticket assignment recorded.');
    }

    public function status(UpdateSupportTicketStatusRequest $request, string $reference): RedirectResponse
    {
        $ticket = $this->support->platformTicket($reference);

        $this->support->transition($ticket, TicketStatus::from($request->validated('status')), $request->user()->id);

        return redirect()
            ->route('platform.support.show', ['reference' => $ticket->ticket_reference])
            ->with('status', 'Ticket status updated.');
    }

    public function storeInternalNote(StoreSupportTicketInternalNoteRequest $request, string $reference): RedirectResponse
    {
        $ticket = $this->support->platformTicket($reference);

        $this->support->addInternalNote($ticket, $request->user()->id, $request->validated('body'));

        return redirect()
            ->route('platform.support.show', ['reference' => $ticket->ticket_reference])
            ->with('status', 'Internal note added.');
    }
}
