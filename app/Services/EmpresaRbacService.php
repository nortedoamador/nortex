<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Cria papéis padrão por empresa e vincula permissões globais.
 */
class EmpresaRbacService
{
    /** @return Collection<int, Role> */
    public function bootstrapEmpresa(Empresa $empresa): Collection
    {
        $permissions = Permission::query()->get()->keyBy('slug');

        $definitions = [
            'administrador' => [
                'name' => 'Administrador',
                'permissions' => $permissions->pluck('slug')->all(),
            ],
            'operador' => [
                'name' => 'Operador',
                'permissions' => [
                    'dashboard.view',
                    'processos.view',
                    'processos.create',
                    'processos.edit',
                    'processos.alterar_status',
                    'clientes.view',
                    'clientes.manage',
                    'embarcacoes.view',
                    'embarcacoes.manage',
                    'habilitacoes.view',
                    'habilitacoes.manage',
                ],
            ],
            'instrutor' => [
                'name' => 'Instrutor',
                'permissions' => ['dashboard.view', 'aulas.view', 'aulas.manage'],
            ],
            'financeiro' => [
                'name' => 'Financeiro',
                'permissions' => ['dashboard.view', 'financeiro.view', 'financeiro.manage'],
            ],
            'cliente' => [
                'name' => 'Cliente',
                'permissions' => ['dashboard.view', 'processos.consulta_propria'],
            ],
        ];

        $roles = collect();

        foreach ($definitions as $slug => $def) {
            $role = Role::query()->firstOrCreate(
                ['empresa_id' => $empresa->id, 'slug' => $slug],
                ['name' => $def['name']],
            );

            $ids = collect($def['permissions'])
                ->map(fn (string $s) => $permissions->get($s)?->id)
                ->filter()
                ->values()
                ->all();

            $role->permissions()->sync($ids);
            $roles->push($role);
        }

        return $roles;
    }

    public function assignRole(User $user, string $roleSlug): void
    {
        $role = Role::query()
            ->where('empresa_id', $user->empresa_id)
            ->where('slug', $roleSlug)
            ->firstOrFail();

        $user->roles()->syncWithoutDetaching([$role->id]);
    }
}
