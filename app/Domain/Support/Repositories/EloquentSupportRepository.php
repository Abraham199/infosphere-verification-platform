<?php

namespace App\Domain\Support\Repositories;

use App\Domain\Support\Interfaces\SupportRepositoryInterface;
use App\Domain\Support\Models\KnowledgeBaseArticle;
use App\Domain\Support\Models\SupportAssignment;
use App\Domain\Support\Models\SupportCategory;
use App\Domain\Support\Models\SupportPriority;
use App\Domain\Support\Models\SupportSlaPolicy;
use App\Domain\Support\Models\SupportTicket;
use App\Domain\Support\Models\SupportTicketAttachment;
use App\Domain\Support\Models\SupportTicketNote;

class EloquentSupportRepository implements SupportRepositoryInterface
{
    public function ticketReferenceExists(string $reference): bool
    {
        return SupportTicket::query()->where('ticket_reference', $reference)->exists();
    }

    public function findTicket(string $ticketId): ?SupportTicket
    {
        return SupportTicket::query()->whereKey($ticketId)->first();
    }

    public function findCategory(?string $tenantId, string $categoryId): ?SupportCategory
    {
        return SupportCategory::query()
            ->whereKey($categoryId)
            ->where(function ($query) use ($tenantId): void {
                $query->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->where('is_active', true)
            ->first();
    }

    public function findPriority(?string $tenantId, string $priorityId): ?SupportPriority
    {
        return SupportPriority::query()
            ->whereKey($priorityId)
            ->where(function ($query) use ($tenantId): void {
                $query->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->where('is_active', true)
            ->first();
    }

    public function activeSlaPolicy(?string $tenantId, ?string $priorityId): ?SupportSlaPolicy
    {
        return SupportSlaPolicy::query()
            ->where('is_active', true)
            ->where(function ($query) use ($tenantId): void {
                $query->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->when($priorityId, fn ($query) => $query->where(function ($query) use ($priorityId): void {
                $query->where('support_priority_id', $priorityId)->orWhereNull('support_priority_id');
            }))
            ->orderByRaw('CASE WHEN tenant_id IS NOT NULL THEN 1 ELSE 0 END DESC')
            ->orderByRaw('CASE WHEN support_priority_id IS NOT NULL THEN 1 ELSE 0 END DESC')
            ->first();
    }

    public function createTicket(array $attributes): SupportTicket
    {
        return SupportTicket::query()->create($attributes);
    }

    public function createAssignment(array $attributes): SupportAssignment
    {
        return SupportAssignment::query()->create($attributes);
    }

    public function createNote(array $attributes): SupportTicketNote
    {
        return SupportTicketNote::query()->create($attributes);
    }

    public function createAttachment(array $attributes): SupportTicketAttachment
    {
        return SupportTicketAttachment::query()->create($attributes);
    }

    public function createSlaPolicy(array $attributes): SupportSlaPolicy
    {
        return SupportSlaPolicy::query()->create($attributes);
    }

    public function createKnowledgeArticle(array $attributes): KnowledgeBaseArticle
    {
        return KnowledgeBaseArticle::query()->create($attributes);
    }
}
