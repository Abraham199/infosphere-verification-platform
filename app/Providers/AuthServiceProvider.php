<?php

namespace App\Providers;

use App\Domain\Tenancy\Models\Tenant;
use App\Domain\Tenancy\Policies\TenantPolicy;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Tenant::class => TenantPolicy::class,
        User::class => UserPolicy::class,
        Role::class => \App\Policies\RolePolicy::class,
        Permission::class => \App\Policies\PermissionPolicy::class,
    ];
}
