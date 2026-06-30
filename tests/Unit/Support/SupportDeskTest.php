<?php

namespace Tests\Unit\Support;

use App\Domain\Support\DTOs\AssignmentData;
use App\Domain\Support\DTOs\AttachmentData;
use App\Domain\Support\DTOs\KnowledgeArticleData;
use App\Domain\Support\DTOs\SlaPolicyData;
use App\Domain\Support\DTOs\TicketData;
use App\Domain\Support\DTOs\TicketNoteData;
use App\Domain\Support\Enums\AssignmentType;
use App\Domain\Support\Enums\KnowledgeArticleStatus;
use App\Domain\Support\Enums\TicketStatus;
use App\Domain\Support\Exceptions\DuplicateTicketReferenceException;
use App\Domain\Support\Exceptions\InvalidSupportConfigurationException;
use App\Domain\Support\Exceptions\InvalidTicketStatusTransitionException;
use App\Domain\Support\Models\KnowledgeBaseCategory;
use App\Domain\Support\Models\SupportCategory;
use App\Domain\Support\Models\SupportPriority;
use App\Domain\Support\Services\AssignmentEngine;
use App\Domain\Support\Services\AttachmentMetadataService;
use App\Domain\Support\Services\KnowledgeBaseService;
use App\Domain\Support\Services\SlaEngine;
use App\Domain\Support\Services\TicketNoteService;
use App\Domain\Support\Services\TicketService;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SupportDeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_creation_applies_reference_status_and_sla(): void
    {
        [$tenant, $category, $priority] = $this->foundation();
        app(SlaEngine::class)->createPolicy(new SlaPolicyData('Normal SLA', 60, 1440, 720, $tenant->id, $priority->id));

        $ticket = app(TicketService::class)->create(new TicketData(
            subject: 'Payment failed',
            description: 'Customer payment failed at checkout.',
            tenantId: $tenant->id,
            categoryId: $category->id,
            priorityId: $priority->id,
            reference: 'TCK-001',
        ));

        $this->assertSame('TCK-001', $ticket->ticket_reference);
        $this->assertSame(TicketStatus::OPEN, $ticket->status);
        $this->assertNotNull($ticket->first_response_due_at);
        $this->assertNotNull($ticket->resolution_due_at);
    }

    public function test_manual_assignment_sets_ticket_assigned(): void
    {
        [$tenant, $category, $priority] = $this->foundation();
        $ticket = $this->ticket($tenant->id, $category->id, $priority->id);

        $assignment = app(AssignmentEngine::class)->assign(new AssignmentData(
            ticketId: $ticket->id,
            assignmentType: AssignmentType::TEAM,
            assignedTeam: 'support-tier-1',
        ));

        $this->assertSame('support-tier-1', $assignment->assigned_team);
        $this->assertSame(TicketStatus::ASSIGNED, $ticket->refresh()->status);
    }

    public function test_valid_status_transition_resolves_ticket(): void
    {
        [$tenant, $category, $priority] = $this->foundation();
        $ticket = $this->ticket($tenant->id, $category->id, $priority->id);
        $ticket->forceFill(['status' => TicketStatus::IN_PROGRESS])->save();

        app(TicketService::class)->transition($ticket, TicketStatus::RESOLVED);

        $this->assertSame(TicketStatus::RESOLVED, $ticket->refresh()->status);
        $this->assertNotNull($ticket->resolved_at);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        [$tenant, $category, $priority] = $this->foundation();
        $ticket = $this->ticket($tenant->id, $category->id, $priority->id);

        $this->expectException(InvalidTicketStatusTransitionException::class);
        app(TicketService::class)->transition($ticket, TicketStatus::CLOSED);
    }

    public function test_sla_breach_is_detected(): void
    {
        [$tenant, $category, $priority] = $this->foundation();
        $ticket = $this->ticket($tenant->id, $category->id, $priority->id);
        $ticket->forceFill(['first_response_due_at' => now()->subMinute()])->save();

        $this->assertTrue(app(SlaEngine::class)->isBreached($ticket));
        $this->assertNotNull($ticket->refresh()->sla_breached_at);
    }

    public function test_internal_note_is_staff_only_metadata(): void
    {
        [$tenant, $category, $priority] = $this->foundation();
        $ticket = $this->ticket($tenant->id, $category->id, $priority->id);

        $note = app(TicketNoteService::class)->create(new TicketNoteData($ticket->id, 'Investigating provider logs.', internal: true));

        $this->assertTrue($note->is_internal);
    }

    public function test_attachment_metadata_is_recorded_without_file_storage_provider(): void
    {
        [$tenant, $category, $priority] = $this->foundation();
        $ticket = $this->ticket($tenant->id, $category->id, $priority->id);

        $attachment = app(AttachmentMetadataService::class)->create(new AttachmentData(
            ticketId: $ticket->id,
            filename: 'receipt.png',
            mimeType: 'image/png',
            sizeBytes: 2048,
            storagePath: 'support/receipts/receipt.png',
        ));

        $this->assertSame('receipt.png', $attachment->filename);
        $this->assertSame('support/receipts/receipt.png', $attachment->storage_path);
    }

    public function test_knowledge_article_can_be_created_and_published(): void
    {
        $tenant = $this->tenant();
        $category = KnowledgeBaseCategory::query()->create([
            'tenant_id' => $tenant->id,
            'slug' => 'payments',
            'name' => 'Payments',
            'is_active' => true,
        ]);

        $article = app(KnowledgeBaseService::class)->createArticle(new KnowledgeArticleData(
            categoryId: $category->id,
            slug: 'payment-failed',
            title: 'Payment Failed',
            body: 'Steps to resolve failed payments.',
            tenantId: $tenant->id,
        ));

        app(KnowledgeBaseService::class)->publish($article);

        $this->assertSame(KnowledgeArticleStatus::PUBLISHED, $article->refresh()->status);
        $this->assertNotNull($article->published_at);
    }

    public function test_duplicate_ticket_reference_is_rejected(): void
    {
        [$tenant, $category, $priority] = $this->foundation();
        $this->ticket($tenant->id, $category->id, $priority->id, 'TCK-DUPLICATE');

        $this->expectException(DuplicateTicketReferenceException::class);
        $this->ticket($tenant->id, $category->id, $priority->id, 'TCK-DUPLICATE');
    }

    public function test_invalid_sla_configuration_is_rejected(): void
    {
        $this->expectException(InvalidSupportConfigurationException::class);

        app(SlaEngine::class)->createPolicy(new SlaPolicyData('Bad SLA', 0, 10));
    }

    private function ticket(string $tenantId, string $categoryId, string $priorityId, ?string $reference = null)
    {
        return app(TicketService::class)->create(new TicketData(
            subject: 'Verification issue',
            description: 'Verification result did not return.',
            tenantId: $tenantId,
            categoryId: $categoryId,
            priorityId: $priorityId,
            reference: $reference,
        ));
    }

    private function foundation(): array
    {
        $tenant = $this->tenant();

        $category = SupportCategory::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'verification',
            'name' => 'Verification',
            'is_active' => true,
        ]);

        $priority = SupportPriority::query()->create([
            'tenant_id' => $tenant->id,
            'code' => 'normal',
            'name' => 'Normal',
            'level' => 2,
            'is_active' => true,
        ]);

        return [$tenant, $category, $priority];
    }

    private function tenant(): Tenant
    {
        return Tenant::query()->create([
            'name' => 'Support Test Tenant',
            'slug' => 'support-test-'.Str::lower((string) Str::ulid()),
            'status' => 'active',
        ]);
    }
}
