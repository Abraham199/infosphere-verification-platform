<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermissionTo('permissions.view');
    }

    public function view(User $actor, Permission $permission): bool
    {
        return $actor->hasPermissionTo('permissions.view');
    }
}
