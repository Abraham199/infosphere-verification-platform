<?php

namespace Tests\Feature\Ui;

use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Services\TenantContext;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SprintOneExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_pages_render(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in');
        $this->get('/forgot-password')->assertOk()->assertSee('Forgot password');
        $this->get('/reset-password/demo-token')->assertOk()->assertSee('Reset password');
    }

    public function test_platform_dashboard_and_module_pages_render_for_super_admin(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('platform.dashboard'))->assertOk()->assertSee('Platform Overview');
        $this->actingAs($admin)->get(route('platform.wallet.index'))->assertOk()->assertSee('Wallet Operations');
        $this->actingAs($admin)->get(route('platform.verification.index'))->assertOk()->assertSee('Verification Services');
        $this->actingAs($admin)->get(route('platform.products.index'))->assertOk()->assertSee('Product Management');
    }

    public function test_tenant_dashboard_and_module_pages_render(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Sprint Tenant',
            'slug' => 'sprint-tenant-'.Str::lower((string) Str::ulid()),
            'status' => 'active',
        ]);
        $user = $this->tenantUser($tenant);

        $this->actingAs($user)->get(route('tenant.dashboard', ['tenant' => $tenant]))->assertOk()->assertSee('Tenant Dashboard');
        $this->actingAs($user)->get(route('tenant.wallet.index', ['tenant' => $tenant]))->assertOk()->assertSee('Wallet');
        $this->actingAs($user)->get(route('tenant.verification.index', ['tenant' => $tenant]))->assertOk()->assertSee('Verification');
        $this->actingAs($user)->get(route('tenant.products.index', ['tenant' => $tenant]))->assertOk()->assertSee('Products');
        $this->actingAs($user)->get(route('tenant.customer.dashboard', ['tenant' => $tenant]))->assertOk()->assertSee('Customer Portal Foundation');
    }

    private function superAdmin(): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::query()->create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $user = User::factory()->create(['user_type' => 'platform']);
        $user->assignRole($role);

        return $user;
    }

    private function tenantUser(Tenant $tenant): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::query()->create(['name' => 'dashboard.view', 'guard_name' => 'web']);
        app(TenantContext::class)->set($tenant);

        $role = Role::query()->create(['tenant_id' => $tenant->id, 'name' => 'Tenant Staff', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $user->assignRole($role);

        return $user;
    }
}
