<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;

/**
 * Evita remover o último administrador da empresa.
 */
final class EquipeAdminService
{
    public function usuarioEhAdministradorNaEmpresa(User $user, int $empresaId): bool
    {
        return $user->roles()
            ->where('roles.empresa_id', $empresaId)
            ->where('slug', 'administrador')
            ->exists();
    }

    public function contarAdministradoresNaEmpresa(int $empresaId): int
    {
        $role = Role::query()
            ->where('empresa_id', $empresaId)
            ->where('slug', 'administrador')
            ->first();

        if (! $role) {
            return 0;
        }

        return $role->users()->where('users.empresa_id', $empresaId)->count();
    }

    /**
     * @param  list<int>  $novosRoleIds
     */
    public function removerAdministradorDeixaEmpresaSemAdmin(User $user, int $empresaId, array $novosRoleIds): bool
    {
        if (! $this->usuarioEhAdministradorNaEmpresa($user, $empresaId)) {
            return false;
        }

        $aindaTeraAdmin = Role::query()
            ->where('empresa_id', $empresaId)
            ->whereIn('id', $novosRoleIds)
            ->where('slug', 'administrador')
            ->exists();

        if ($aindaTeraAdmin) {
            return false;
        }

        return $this->contarAdministradoresNaEmpresa($empresaId) <= 1;
    }

    /** Excluir este utilizador removeria o último administrador da empresa. */
    public function exclusaoRemoveUltimoAdministrador(User $alvo, int $empresaId): bool
    {
        if (! $this->usuarioEhAdministradorNaEmpresa($alvo, $empresaId)) {
            return false;
        }

        return $this->contarAdministradoresNaEmpresa($empresaId) <= 1;
    }
}
