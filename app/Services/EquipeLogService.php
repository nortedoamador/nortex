<?php

namespace App\Services;

use App\Models\EquipeLog;
use App\Models\Role;
use App\Models\User;

final class EquipeLogService
{
    /**
     * @param  list<int|string>  $roleIds
     * @return list<string>
     */
    public function nomesPapeis(int $empresaId, array $roleIds): array
    {
        $ids = collect($roleIds)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
        if ($ids === []) {
            return [];
        }

        return Role::query()
            ->where('empresa_id', $empresaId)
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();
    }

    public function registrar(
        int $empresaId,
        ?User $actor,
        ?User $subject,
        string $action,
        string $summary,
        ?array $meta = null,
    ): void {
        EquipeLog::query()->create([
            'empresa_id' => $empresaId,
            'actor_id' => $actor?->id,
            'subject_user_id' => $subject?->id,
            'action' => $action,
            'summary' => $summary,
            'meta' => $meta,
            'created_at' => now(),
        ]);
    }

    /**
     * @param  list<int|string>  $novosRoleIds
     * @return array<string, mixed>
     */
    public function metaAlteracoesUsuario(User $antes, array $data, array $novosRoleIds, int $empresaId): array
    {
        $meta = [];

        if ($antes->name !== $data['name']) {
            $meta['name'] = ['de' => $antes->name, 'para' => $data['name']];
        }
        if ($antes->email !== $data['email']) {
            $meta['email'] = ['de' => $antes->email, 'para' => $data['email']];
        }
        if (! empty($data['password'])) {
            $meta['password'] = ['alterada' => true];
        }

        $idsAntes = $antes->roles->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $idsDepois = collect($novosRoleIds)->map(fn ($id) => (int) $id)->sort()->values()->all();
        if ($idsAntes !== $idsDepois) {
            $meta['roles'] = [
                'anteriores' => $this->nomesPapeis($empresaId, $idsAntes),
                'novos' => $this->nomesPapeis($empresaId, $idsDepois),
            ];
        }

        return $meta;
    }
}
