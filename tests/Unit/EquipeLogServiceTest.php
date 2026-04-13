<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use App\Services\EquipeLogService;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class EquipeLogServiceTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_nomes_papeis_vazio_quando_sem_ids(): void
    {
        $empresa = Empresa::factory()->create();
        User::factory()->create(['empresa_id' => $empresa->id]);

        $service = app(EquipeLogService::class);
        $this->assertSame([], $service->nomesPapeis($empresa->id, []));
        $this->assertSame([], $service->nomesPapeis($empresa->id, ['', '0']));
    }

    public function test_nomes_papeis_ordena_por_nome(): void
    {
        $empresa = Empresa::factory()->create();
        User::factory()->create(['empresa_id' => $empresa->id]);

        $operador = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'operador')
            ->firstOrFail();
        $admin = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'administrador')
            ->firstOrFail();

        $service = app(EquipeLogService::class);
        $nomes = $service->nomesPapeis($empresa->id, [$operador->id, $admin->id]);

        $this->assertSame(['Administrador', 'Operador'], $nomes);
    }

    public function test_meta_alteracoes_usuario_sem_mudancas_retorna_vazio(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        $user->load('roles');

        $operador = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'operador')
            ->firstOrFail();
        $user->roles()->sync([$operador->id]);
        $user->load('roles');

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
        ];

        $service = app(EquipeLogService::class);
        $meta = $service->metaAlteracoesUsuario($user, $data, [(string) $operador->id], $empresa->id);

        $this->assertSame([], $meta);
    }

    public function test_meta_alteracoes_usuario_inclui_papeis_por_nome_quando_roles_mudam(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $operador = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'operador')
            ->firstOrFail();
        $financeiro = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'financeiro')
            ->firstOrFail();

        $user->roles()->sync([$operador->id]);
        $user->load('roles');

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
        ];

        $service = app(EquipeLogService::class);
        $meta = $service->metaAlteracoesUsuario($user, $data, [(string) $financeiro->id], $empresa->id);

        $this->assertArrayHasKey('roles', $meta);
        $this->assertSame(['Operador'], $meta['roles']['anteriores']);
        $this->assertSame(['Financeiro'], $meta['roles']['novos']);
    }
}
