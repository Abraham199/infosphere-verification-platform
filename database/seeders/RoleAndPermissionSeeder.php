<?php

namespace Database\Seeders;

use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'tenants.view' => 'tenancy',
            'tenants.create' => 'tenancy',
            'tenants.update' => 'tenancy',
            'tenant.profile.view' => 'tenancy',
            'tenant.profile.update' => 'tenancy',
            'users.view' => 'identity',
            'users.create' => 'identity',
            'users.update' => 'identity',
            'roles.view' => 'identity',
            'roles.create' => 'identity',
            'roles.update' => 'identity',
            'permissions.view' => 'identity',
            'dashboard.view' => 'dashboard',
            'support.tickets.view' => 'support',
            'support.tickets.create' => 'support',
            'support.tickets.update' => 'support',
            'support.tickets.comment' => 'support',
            'notifications.view' => 'notifications',
            'notifications.manage' => 'notifications',
            'notifications.send' => 'notifications',
            'notifications.preferences' => 'notifications',
            'settings.view' => 'settings',
            'settings.update' => 'settings',
        ];

        foreach ($permissions as $name => $module) {
            Permission::query()->firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['module' => $module],
            );
        }

        $superAdmin = Role::query()->firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $tenantOwner = Role::query()->firstOrCreate(['name' => 'Tenant Owner', 'guard_name' => 'web']);
        $tenantAdmin = Role::query()->firstOrCreate(['name' => 'Tenant Admin', 'guard_name' => 'web']);
        $staff = Role::query()->firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);

        $superAdmin->syncPermissions(Permission::all());
        $tenantOwner->syncPermissions(Permission::whereIn('name', [
            'tenant.profile.view',
            'tenant.profile.update',
            'users.view',
            'users.create',
            'users.update',
            'roles.view',
            'dashboard.view',
            'support.tickets.view',
            'support.tickets.create',
            'support.tickets.update',
            'support.tickets.comment',
            'notifications.view',
            'notifications.manage',
            'notifications.send',
            'notifications.preferences',
            'settings.view',
            'settings.update',
        ])->get());
        $tenantAdmin->syncPermissions(Permission::whereIn('name', [
            'tenant.profile.view',
            'users.view',
            'dashboard.view',
            'support.tickets.view',
            'support.tickets.create',
            'support.tickets.comment',
            'notifications.view',
            'notifications.preferences',
            'settings.view',
        ])->get());
        $staff->syncPermissions(Permission::whereIn('name', [
            'dashboard.view',
            'support.tickets.view',
            'support.tickets.create',
            'support.tickets.comment',
            'notifications.view',
            'notifications.preferences',
        ])->get());
    }
}
