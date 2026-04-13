<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_platform_admin || $user->hasPermission('roles.manage');
    }

    public function view(User $user, Role $role): bool
    {
        return $this->managesRole($user, $role);
    }

    public function create(User $user): bool
    {
        return $user->is_platform_admin || $user->hasPermission('roles.manage');
    }

    public function update(User $user, Role $role): bool
    {
        return $this->managesRole($user, $role);
    }

    public function delete(User $user, Role $role): bool
    {
        return $this->managesRole($user, $role);
    }

    private function managesRole(User $user, Role $role): bool
    {
        if ($user->is_platform_admin) {
            return true;
        }

        if (! $user->hasPermission('roles.manage')) {
            return false;
        }

        return (int) $user->empresa_id === (int) $role->empresa_id;
    }
}
