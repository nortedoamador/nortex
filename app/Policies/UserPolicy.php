<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('usuarios.manage');
    }

    public function view(User $actor, User $target): bool
    {
        return $this->update($actor, $target);
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission('usuarios.manage');
    }

    public function update(User $actor, User $target): bool
    {
        if (! $actor->hasPermission('usuarios.manage')) {
            return false;
        }

        return (int) $actor->empresa_id === (int) $target->empresa_id;
    }

    public function delete(User $actor, User $target): bool
    {
        if ((int) $actor->id === (int) $target->id) {
            return false;
        }

        return $this->update($actor, $target);
    }
}
