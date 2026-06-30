<?php

namespace Tests\Feature;

use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_can_receive_permission_and_be_assigned_to_user(): void
    {
        $permission = Permission::create(['name' => 'dashboard.view', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'Staff', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->assertTrue($user->hasPermissionTo('dashboard.view'));
    }
}
