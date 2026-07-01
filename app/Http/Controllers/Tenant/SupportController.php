<?php

namespace App\Http\Controllers\Tenant;

use App\Domain\Tenancy\Services\TenantContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreSupportTicketNoteRequest;
use App\Http\Requests\Tenant\StoreSupportTicketRequest;
use App\Services\SupportDeskWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly SupportDeskWorkflowService $support,
    ) {
    }

    public function index(Request $request): View
    {
        $tenant = $this->tenantContext->require();
        $status = $request->query('status');

        return view('tenant.support.index', [
            'tenant' => $tenant,
            'tickets' => $this->support->tenantTickets($tenant->id, is_string($status) ? $status : null),
            'categories' => $this->support->categories($tenant->id),
            'priorities' => $this->support->priorities($tenant->id),
            'articles' => $this->support->publishedArticles($tenant->id),
            'status' => $status,
        ]);
    }

    public function store(StoreSupportTicketRequest $request): RedirectResponse
    {
        $tenant = $this->tenantContext->require();

        $ticket = $this->support->createTenantTicket(
            tenantId: $tenant->id,
            requesterId: $request->user()->id,
            data: $request->validated(),
        );

        return redirect()
            ->route('tenant.support.show', ['tenant' => $tenant, 'reference' => $ticket->ticket_reference])
            ->with('status', 'Support ticket created.');
    }

    public function show(string $tenantSlug, string $reference): View
    {
        $tenant = $this->tenantContext->require();
        $ticket = $this->support->tenantTicket($tenant->id, $reference);
        $isBreached = $this->support->markSlaState($ticket);

        return view('tenant.support.show', [
            'tenant' => $tenant,
            'ticket' => $ticket->refresh()->load(['category', 'priority', 'slaPolicy', 'assignments.assignee', 'notes.author', 'attachments']),
            'isBreached' => $isBreached,
        ]);
    }

    public function storeNote(StoreSupportTicketNoteRequest $request, string $tenantSlug, string $reference): RedirectResponse
    {
        $tenant = $this->tenantContext->require();
        $ticket = $this->support->tenantTicket($tenant->id, $reference);

        $this->support->addTenantNote($ticket, $request->user()->id, $request->validated('body'));

        return redirect()
            ->route('tenant.support.show', ['tenant' => $tenant, 'reference' => $ticket->ticket_reference])
            ->with('status', 'Reply added.');
    }
}
