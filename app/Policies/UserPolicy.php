<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermissionTo('users.view');
    }

    public function view(User $actor, User $subject): bool
    {
        return $actor->isPlatformUser()
            || ($actor->tenant_id !== null && $actor->tenant_id === $subject->tenant_id && $actor->hasPermissionTo('users.view'));
    }

    public function update(User $actor, User $subject): bool
    {
        return $actor->isPlatformUser()
            || ($actor->tenant_id !== null && $actor->tenant_id === $subject->tenant_id && $actor->hasPermissionTo('users.update'));
    }
}
