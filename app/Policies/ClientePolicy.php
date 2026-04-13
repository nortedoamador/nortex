<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;

class ClientePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('clientes.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('clientes.manage');
    }

    public function view(User $user, Cliente $cliente): bool
    {
        return $user->hasPermission('clientes.view')
            && (int) $user->empresa_id === (int) $cliente->empresa_id;
    }

    public function manage(User $user, Cliente $cliente): bool
    {
        return $user->hasPermission('clientes.manage')
            && (int) $user->empresa_id === (int) $cliente->empresa_id;
    }

    public function update(User $user, Cliente $cliente): bool
    {
        return $this->manage($user, $cliente);
    }

    public function delete(User $user, Cliente $cliente): bool
    {
        return $this->manage($user, $cliente);
    }
}
