<?php

namespace App\Policies;

use App\Models\Embarcacao;
use App\Models\User;

class EmbarcacaoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('embarcacoes.view');
    }

    public function view(User $user, Embarcacao $embarcacao): bool
    {
        return $user->hasPermission('embarcacoes.view')
            && (int) $user->empresa_id === (int) $embarcacao->empresa_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('embarcacoes.manage');
    }

    public function update(User $user, Embarcacao $embarcacao): bool
    {
        return $this->manage($user, $embarcacao);
    }

    public function manage(User $user, Embarcacao $embarcacao): bool
    {
        return $user->hasPermission('embarcacoes.manage')
            && (int) $user->empresa_id === (int) $embarcacao->empresa_id;
    }
}
