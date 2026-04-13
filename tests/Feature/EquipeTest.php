<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EquipeLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Tests\Concerns\SafeRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EquipeTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_operador_nao_acessa_equipe(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);
        $user->roles()->detach();

        $operador = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'operador')
            ->firstOrFail();
        $user->roles()->attach($operador->id);

        $this->actingAs($user)
            ->get(route('equipe.index'))
            ->assertForbidden();
    }

    public function test_administrador_lista_equipe(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('equipe.index'))
            ->assertOk()
            ->assertSee($user->email);
    }

    public function test_administrador_cria_usuario_com_papeis(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        $operador = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'operador')
            ->firstOrFail();

        $this->actingAs($admin);

        $response = $this->post(route('equipe.store'), [
            'name' => 'Novo Colaborador',
            'email' => 'novo@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'roles' => [(string) $operador->id],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('equipe.index'));

        $novo = User::query()->where('email', 'novo@example.com')->firstOrFail();
        $this->assertTrue($novo->roles()->where('slug', 'operador')->exists());

        $this->assertDatabaseHas('equipe_logs', [
            'empresa_id' => $empresa->id,
            'actor_id' => $admin->id,
            'subject_user_id' => $novo->id,
            'action' => 'user_created',
        ]);

        $logCriacao = EquipeLog::query()
            ->where('action', 'user_created')
            ->where('subject_user_id', $novo->id)
            ->firstOrFail();
        $this->assertSame(['Operador'], $logCriacao->meta['papeis']);
        $this->assertArrayNotHasKey('convite_por_email', $logCriacao->meta);
    }

    public function test_cria_usuario_com_convite_envia_notificacao_de_reset(): void
    {
        Notification::fake();

        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        $operador = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'operador')
            ->firstOrFail();

        $this->actingAs($admin)->post(route('equipe.store'), [
            'name' => 'Usuário Convidado',
            'email' => 'convidado@example.com',
            'enviar_convite' => '1',
            'roles' => [(string) $operador->id],
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('equipe.index'));

        $novo = User::query()->where('email', 'convidado@example.com')->firstOrFail();
        Notification::assertSentTo($novo, ResetPassword::class);

        $log = EquipeLog::query()
            ->where('action', 'user_created')
            ->where('subject_user_id', $novo->id)
            ->firstOrFail();
        $this->assertTrue($log->meta['convite_por_email'] ?? false);
    }

    public function test_administrador_pode_enviar_link_reset_na_edicao(): void
    {
        Notification::fake();

        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        $operadorRole = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'operador')
            ->firstOrFail();

        $colaborador = User::query()->create([
            'empresa_id' => $empresa->id,
            'name' => 'Colab',
            'email' => 'colab-reset@example.com',
            'password' => bcrypt('Password1!'),
        ]);
        $colaborador->roles()->sync([$operadorRole->id]);

        $this->actingAs($admin)
            ->post(route('equipe.password-reset', $colaborador))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('equipe.edit', $colaborador));

        Notification::assertSentTo($colaborador, ResetPassword::class);
    }

    public function test_nao_permite_remover_ultimo_administrador(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        $operador = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'operador')
            ->firstOrFail();

        $adminRole = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'administrador')
            ->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('equipe.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'roles' => [$operador->id],
            ])
            ->assertSessionHasErrors('roles');

        $this->assertTrue($admin->fresh()->roles()->where('roles.id', $adminRole->id)->exists());
    }

    public function test_administrador_atualiza_dados_do_colaborador(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        $operadorRole = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'operador')
            ->firstOrFail();

        $colaborador = User::query()->create([
            'empresa_id' => $empresa->id,
            'name' => 'Colaborador',
            'email' => 'colab@example.com',
            'password' => bcrypt('OldPassword1!'),
        ]);
        $colaborador->roles()->sync([$operadorRole->id]);

        $this->actingAs($admin)
            ->patch(route('equipe.update', $colaborador), [
                'name' => 'Colaborador Atualizado',
                'email' => 'colab-novo@example.com',
                'password' => 'NewPassword1!',
                'password_confirmation' => 'NewPassword1!',
                'roles' => [$operadorRole->id],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('equipe.index'));

        $colaborador->refresh();
        $this->assertSame('Colaborador Atualizado', $colaborador->name);
        $this->assertSame('colab-novo@example.com', $colaborador->email);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewPassword1!', $colaborador->password));

        $this->assertTrue(
            EquipeLog::query()
                ->where('empresa_id', $empresa->id)
                ->where('action', 'user_updated')
                ->where('subject_user_id', $colaborador->id)
                ->exists()
        );
    }

    public function test_administrador_remove_membro_e_regista_log(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        $operadorRole = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'operador')
            ->firstOrFail();

        $membro = User::query()->create([
            'empresa_id' => $empresa->id,
            'name' => 'A Remover',
            'email' => 'remover@example.com',
            'password' => bcrypt('Password1!'),
        ]);
        $membro->roles()->sync([$operadorRole->id]);

        $this->actingAs($admin)
            ->delete(route('equipe.destroy', $membro))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('equipe.index'));

        $this->assertNull(User::query()->where('email', 'remover@example.com')->first());

        $log = EquipeLog::query()
            ->where('empresa_id', $empresa->id)
            ->where('action', 'user_deleted')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($admin->id, $log->actor_id);
        $this->assertSame('A Remover', $log->meta['removido']['nome']);
        $this->assertSame('remover@example.com', $log->meta['removido']['email']);
        $this->assertSame(['Operador'], $log->meta['removido']['papeis']);
    }

    public function test_administrador_nao_pode_remover_a_propria_conta(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($admin)
            ->delete(route('equipe.destroy', $admin))
            ->assertForbidden();
    }

    public function test_nao_permite_remover_ultimo_administrador_via_exclusao(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        $operadorRole = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'operador')
            ->firstOrFail();

        $operadorComGerir = User::factory()->create(['empresa_id' => $empresa->id]);
        $operadorComGerir->roles()->sync([$operadorRole->id]);

        // Depois de criar o usuário: bootstrapEmpresa volta a fazer sync nas permissões do operador.
        $manage = Permission::query()->where('slug', 'usuarios.manage')->firstOrFail();
        $operadorRole->permissions()->attach($manage->id);

        $this->actingAs($operadorComGerir->fresh());

        $this->from(route('equipe.index'))
            ->delete(route('equipe.destroy', $admin))
            ->assertRedirect(route('equipe.index'))
            ->assertSessionHasErrors('delete');

        $this->assertNotNull(User::query()->whereKey($admin->id)->first());
    }

    public function test_administrador_exporta_registos_csv(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        EquipeLog::query()->create([
            'empresa_id' => $empresa->id,
            'actor_id' => $admin->id,
            'subject_user_id' => $admin->id,
            'action' => 'user_updated',
            'summary' => 'Resumo único export',
            'meta' => ['k' => 'v'],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('equipe.logs.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('data_hora', $response->streamedContent());
        $this->assertStringContainsString('Resumo único export', $response->streamedContent());
        $this->assertStringContainsString('user_updated', $response->streamedContent());
    }

    public function test_filtra_registos_por_tipo_na_listagem(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        EquipeLog::query()->create([
            'empresa_id' => $empresa->id,
            'actor_id' => $admin->id,
            'subject_user_id' => $admin->id,
            'action' => 'user_created',
            'summary' => 'SUMMARY_CREATED_ONLY',
            'meta' => null,
            'created_at' => now(),
        ]);
        EquipeLog::query()->create([
            'empresa_id' => $empresa->id,
            'actor_id' => $admin->id,
            'subject_user_id' => $admin->id,
            'action' => 'user_updated',
            'summary' => 'SUMMARY_UPDATED_ONLY',
            'meta' => null,
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('equipe.index', ['acao' => 'user_created']))
            ->assertOk()
            ->assertSee('SUMMARY_CREATED_ONLY', false)
            ->assertDontSee('SUMMARY_UPDATED_ONLY', false);
    }

    public function test_segunda_pagina_de_registos(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        for ($i = 0; $i < 16; $i++) {
            EquipeLog::query()->create([
                'empresa_id' => $empresa->id,
                'actor_id' => $admin->id,
                'subject_user_id' => $admin->id,
                'action' => 'user_updated',
                'summary' => 'LOG_PAGE_'.$i,
                'meta' => null,
                'created_at' => now()->subSecond($i),
            ]);
        }

        $this->actingAs($admin)
            ->get(route('equipe.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('LOG_PAGE_0', false);
    }
}
