<?php

namespace App\Domain\Support\Interfaces;

use App\Domain\Support\Models\KnowledgeBaseArticle;
use App\Domain\Support\Models\SupportAssignment;
use App\Domain\Support\Models\SupportCategory;
use App\Domain\Support\Models\SupportPriority;
use App\Domain\Support\Models\SupportSlaPolicy;
use App\Domain\Support\Models\SupportTicket;
use App\Domain\Support\Models\SupportTicketAttachment;
use App\Domain\Support\Models\SupportTicketNote;

interface SupportRepositoryInterface
{
    public function ticketReferenceExists(string $reference): bool;
    public function findTicket(string $ticketId): ?SupportTicket;
    public function findCategory(?string $tenantId, string $categoryId): ?SupportCategory;
    public function findPriority(?string $tenantId, string $priorityId): ?SupportPriority;
    public function activeSlaPolicy(?string $tenantId, ?string $priorityId): ?SupportSlaPolicy;
    public function createTicket(array $attributes): SupportTicket;
    public function createAssignment(array $attributes): SupportAssignment;
    public function createNote(array $attributes): SupportTicketNote;
    public function createAttachment(array $attributes): SupportTicketAttachment;
    public function createSlaPolicy(array $attributes): SupportSlaPolicy;
    public function createKnowledgeArticle(array $attributes): KnowledgeBaseArticle;
}
