<?php

namespace App\Policies;

use App\Models\Habilitacao;
use App\Models\User;

class HabilitacaoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('habilitacoes.view');
    }

    public function view(User $user, Habilitacao $habilitacao): bool
    {
        return $user->hasPermission('habilitacoes.view')
            && (int) $user->empresa_id === (int) $habilitacao->empresa_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('habilitacoes.manage');
    }

    public function update(User $user, Habilitacao $habilitacao): bool
    {
        return $this->manage($user, $habilitacao);
    }

    public function manage(User $user, Habilitacao $habilitacao): bool
    {
        return $user->hasPermission('habilitacoes.manage')
            && (int) $user->empresa_id === (int) $habilitacao->empresa_id;
    }
}
