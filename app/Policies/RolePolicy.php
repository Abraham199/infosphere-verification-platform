<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermissionTo('roles.view');
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermissionTo('roles.create');
    }

    public function update(User $actor, Role $role): bool
    {
        return $actor->hasPermissionTo('roles.update') && $role->name !== 'Super Admin';
    }
}
