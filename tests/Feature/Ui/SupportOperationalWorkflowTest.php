<?php

namespace Tests\Feature\Ui;

use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Support\DTOs\TicketData;
use App\Domain\Support\Enums\TicketStatus;
use App\Domain\Support\Models\SupportCategory;
use App\Domain\Support\Models\SupportPriority;
use App\Domain\Support\Models\SupportTicket;
use App\Domain\Support\Services\TicketService;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Services\TenantContext;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SupportOperationalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_create_and_view_support_ticket(): void
    {
        [$tenant, $user, $category, $priority] = $this->tenantFixture();

        $this->actingAs($user)
            ->post(route('tenant.support.store', ['tenant' => $tenant]), [
                'subject' => 'Payment receipt missing',
                'description' => 'Customer paid but the receipt is not visible in the portal.',
                'support_category_id' => $category->id,
                'support_priority_id' => $priority->id,
            ])
            ->assertRedirect();

        $ticket = SupportTicket::query()->where('tenant_id', $tenant->id)->firstOrFail();

        $this->assertSame('Payment receipt missing', $ticket->subject);
        $this->assertSame(TicketStatus::OPEN, $ticket->status);

        $this->actingAs($user)
            ->get(route('tenant.support.show', ['tenant' => $tenant, 'reference' => $ticket->ticket_reference]))
            ->assertOk()
            ->assertSee('Payment receipt missing');
    }

    public function test_tenant_user_can_add_public_reply_but_not_see_internal_notes(): void
    {
        [$tenant, $user] = $this->tenantFixture();
        $ticket = $this->ticket($tenant, $user);
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->post(route('platform.support.internal-notes.store', ['reference' => $ticket->ticket_reference]), [
                'body' => 'Escalate to payments desk before customer update.',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('tenant.support.notes.store', ['tenant' => $tenant, 'reference' => $ticket->ticket_reference]), [
                'body' => 'Customer is still waiting for confirmation.',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('tenant.support.show', ['tenant' => $tenant, 'reference' => $ticket->ticket_reference]))
            ->assertOk()
            ->assertSee('Customer is still waiting for confirmation.')
            ->assertDontSee('Escalate to payments desk before customer update.');
    }

    public function test_platform_operator_can_assign_and_transition_ticket(): void
    {
        [$tenant, $tenantUser] = $this->tenantFixture();
        $ticket = $this->ticket($tenant, $tenantUser);
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->post(route('platform.support.assignments.store', ['reference' => $ticket->ticket_reference]), [
                'assignment_type' => 'team',
                'assigned_team' => 'support-tier-2',
            ])
            ->assertRedirect(route('platform.support.show', ['reference' => $ticket->ticket_reference]));

        $this->assertDatabaseHas('support_assignments', [
            'support_ticket_id' => $ticket->id,
            'assigned_team' => 'support-tier-2',
        ]);
        $this->assertSame(TicketStatus::ASSIGNED, $ticket->refresh()->status);

        $this->actingAs($admin)
            ->patch(route('platform.support.status', ['reference' => $ticket->ticket_reference]), [
                'status' => 'in_progress',
            ])
            ->assertRedirect(route('platform.support.show', ['reference' => $ticket->ticket_reference]));

        $this->assertSame(TicketStatus::IN_PROGRESS, $ticket->refresh()->status);
        $this->assertNotNull($ticket->first_responded_at);
    }

    public function test_tenant_isolation_blocks_other_tenant_ticket(): void
    {
        [$tenant, $user] = $this->tenantFixture();
        [$otherTenant, $otherUser] = $this->tenantFixture('support-other');
        $ticket = $this->ticket($otherTenant, $otherUser);

        $this->actingAs($user)
            ->get(route('tenant.support.show', ['tenant' => $tenant, 'reference' => $ticket->ticket_reference]))
            ->assertNotFound();
    }

    public function test_support_routes_respect_rbac(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Blocked Support Tenant',
            'slug' => 'blocked-support-'.Str::lower((string) Str::ulid()),
            'status' => 'active',
        ]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('tenant.support.index', ['tenant' => $tenant]))
            ->assertForbidden();
    }

    private function tenantFixture(string $slugPrefix = 'support-workflow'): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $tenant = Tenant::query()->create([
            'name' => 'Support Workflow Tenant',
            'slug' => $slugPrefix.'-'.Str::lower((string) Str::ulid()),
            'status' => 'active',
        ]);
        app(TenantContext::class)->set($tenant);

        $permissions = collect(['dashboard.view', 'support.tickets.view', 'support.tickets.create', 'support.tickets.comment'])
            ->map(fn (string $name) => Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']));
        $role = Role::query()->create(['tenant_id' => $tenant->id, 'name' => 'Support Operator '.$tenant->id, 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole($role);

        $category = SupportCategory::query()->create([
            'code' => 'payments',
            'name' => 'Payments',
            'is_active' => true,
        ]);
        $priority = SupportPriority::query()->create([
            'code' => 'normal',
            'name' => 'Normal',
            'level' => 2,
            'is_active' => true,
        ]);

        return [$tenant, $user, $category, $priority];
    }

    private function ticket(Tenant $tenant, User $user): SupportTicket
    {
        return app(TicketService::class)->create(new TicketData(
            subject: 'Verification delayed',
            description: 'A submitted verification request has not completed yet.',
            tenantId: $tenant->id,
            requesterId: $user->id,
            source: 'test',
        ));
    }

    private function superAdmin(): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        app(TenantContext::class)->clear();

        $role = Role::query()->firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['user_type' => 'platform']);
        $user->assignRole($role);

        return $user;
    }
}
