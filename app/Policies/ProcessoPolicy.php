<?php

namespace App\Policies;

use App\Models\Processo;
use App\Models\User;

class ProcessoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('processos.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('processos.create');
    }

    public function view(User $user, Processo $processo): bool
    {
        return $user->hasPermission('processos.view')
            && (int) $user->empresa_id === (int) $processo->empresa_id;
    }

    public function updateStatus(User $user, Processo $processo): bool
    {
        return $user->hasPermission('processos.alterar_status')
            && (int) $user->empresa_id === (int) $processo->empresa_id;
    }

    public function updateDocumento(User $user, Processo $processo): bool
    {
        return $user->hasPermission('processos.edit')
            && (int) $user->empresa_id === (int) $processo->empresa_id;
    }

    /** Descarte do rascunho criado pelo modal (mesma permissão que editar checklist). */
    public function delete(User $user, Processo $processo): bool
    {
        return $this->updateDocumento($user, $processo);
    }
}
