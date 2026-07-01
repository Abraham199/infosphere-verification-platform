<?php

namespace App\Services;

use App\Domain\Support\DTOs\AssignmentData;
use App\Domain\Support\DTOs\TicketData;
use App\Domain\Support\DTOs\TicketNoteData;
use App\Domain\Support\Enums\AssignmentType;
use App\Domain\Support\Enums\KnowledgeArticleStatus;
use App\Domain\Support\Enums\TicketStatus;
use App\Domain\Support\Interfaces\AssignmentEngineInterface;
use App\Domain\Support\Interfaces\SlaEngineInterface;
use App\Domain\Support\Interfaces\SupportTicketServiceInterface;
use App\Domain\Support\Models\KnowledgeBaseArticle;
use App\Domain\Support\Models\SupportCategory;
use App\Domain\Support\Models\SupportPriority;
use App\Domain\Support\Models\SupportTicket;
use App\Domain\Support\Services\TicketNoteService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SupportDeskWorkflowService
{
    public function __construct(
        private readonly SupportTicketServiceInterface $tickets,
        private readonly TicketNoteService $notes,
        private readonly AssignmentEngineInterface $assignments,
        private readonly SlaEngineInterface $sla,
    ) {
    }

    public function tenantTickets(string $tenantId, ?string $status = null): LengthAwarePaginator
    {
        return SupportTicket::query()
            ->with(['category', 'priority'])
            ->where('tenant_id', $tenantId)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }

    public function platformTickets(?string $status = null): LengthAwarePaginator
    {
        return SupportTicket::query()
            ->with(['tenant', 'category', 'priority'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function tenantTicket(string $tenantId, string $reference): SupportTicket
    {
        return SupportTicket::query()
            ->with(['category', 'priority', 'slaPolicy', 'assignments.assignee', 'notes.author', 'attachments'])
            ->where('tenant_id', $tenantId)
            ->where('ticket_reference', $reference)
            ->firstOrFail();
    }

    public function platformTicket(string $reference): SupportTicket
    {
        return SupportTicket::query()
            ->with(['tenant', 'requester', 'category', 'priority', 'slaPolicy', 'assignments.assignee', 'notes.author', 'attachments'])
            ->where('ticket_reference', $reference)
            ->firstOrFail();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createTenantTicket(string $tenantId, string $requesterId, array $data): SupportTicket
    {
        return $this->tickets->create(new TicketData(
            subject: $data['subject'],
            description: $data['description'],
            tenantId: $tenantId,
            requesterId: $requesterId,
            categoryId: $data['support_category_id'] ?? null,
            priorityId: $data['support_priority_id'] ?? null,
            source: 'tenant_portal',
            metadata: ['created_from' => 'tenant_support_ui'],
        ));
    }

    public function addTenantNote(SupportTicket $ticket, string $authorId, string $body): void
    {
        $this->notes->create(new TicketNoteData(
            ticketId: $ticket->id,
            body: $body,
            authorId: $authorId,
            internal: false,
            metadata: ['created_from' => 'tenant_support_ui'],
        ));
    }

    public function addInternalNote(SupportTicket $ticket, string $authorId, string $body): void
    {
        $this->notes->create(new TicketNoteData(
            ticketId: $ticket->id,
            body: $body,
            authorId: $authorId,
            internal: true,
            metadata: ['created_from' => 'platform_support_ui'],
        ));
    }

    public function assign(SupportTicket $ticket, AssignmentType $type, ?string $team, ?string $userId, string $assignedBy): void
    {
        $this->assignments->assign(new AssignmentData(
            ticketId: $ticket->id,
            assignmentType: $type,
            assignedToUserId: $userId,
            assignedTeam: $team,
            assignedBy: $assignedBy,
            metadata: ['created_from' => 'platform_support_ui'],
        ));
    }

    public function transition(SupportTicket $ticket, TicketStatus $status, string $actorId): SupportTicket
    {
        return $this->tickets->transition($ticket, $status, $actorId);
    }

    public function markSlaState(SupportTicket $ticket): bool
    {
        return $this->sla->isBreached($ticket);
    }

    public function categories(?string $tenantId): Collection
    {
        return SupportCategory::query()
            ->where('is_active', true)
            ->where(function ($query) use ($tenantId): void {
                $query->whereNull('tenant_id')->orWhere('tenant_id', $tenantId);
            })
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }

    public function priorities(?string $tenantId): Collection
    {
        return SupportPriority::query()
            ->where('is_active', true)
            ->where(function ($query) use ($tenantId): void {
                $query->whereNull('tenant_id')->orWhere('tenant_id', $tenantId);
            })
            ->orderByDesc('level')
            ->orderBy('name')
            ->get();
    }

    public function publishedArticles(?string $tenantId): Collection
    {
        return KnowledgeBaseArticle::query()
            ->with('category')
            ->where('status', KnowledgeArticleStatus::PUBLISHED)
            ->where(function ($query) use ($tenantId): void {
                $query->whereNull('tenant_id')->orWhere('tenant_id', $tenantId);
            })
            ->latest('published_at')
            ->limit(12)
            ->get();
    }
}
