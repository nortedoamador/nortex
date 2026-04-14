<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use App\Services\EmpresaRbacService;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class PlatformEmpresasTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_platform_admin_acessa_listagem_de_empresas(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->get(route('platform.empresas.index'))
            ->assertOk();
    }

    public function test_platform_admin_acessa_painel_da_empresa(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $empresa = Empresa::factory()->create();

        $this->actingAs($admin)
            ->get(route('platform.empresas.show', $empresa))
            ->assertOk();
    }

    public function test_platform_admin_acessa_criacao_de_utilizador_para_empresa(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $empresa = Empresa::factory()->create();

        $this->actingAs($admin)
            ->get(route('platform.usuarios.create', ['empresa_id' => $empresa->id]))
            ->assertOk()
            ->assertSeeText('Novo utilizador');
    }

    public function test_platform_admin_cria_utilizador_para_empresa_com_papel(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $empresa = Empresa::factory()->create();
        app(EmpresaRbacService::class)->bootstrapEmpresa($empresa);

        $papel = Role::query()
            ->where('empresa_id', $empresa->id)
            ->where('slug', 'operador')
            ->firstOrFail();

        $this->actingAs($admin)
            ->post(route('platform.usuarios.store'), [
                'empresa_id' => $empresa->id,
                'name' => 'Usuario Empresa',
                'email' => 'usuario.empresa@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'roles' => [$papel->id],
            ])
            ->assertRedirect();

        $user = User::query()
            ->where('email', 'usuario.empresa@example.com')
            ->first();

        $this->assertNotNull($user);
        $this->assertSame($empresa->id, $user->empresa_id);
        $this->assertTrue($user->roles()->whereKey($papel->id)->exists());
    }

    public function test_admin_consegue_sair_do_impersonate_mesmo_com_usuario_nao_verificado(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $alvo = User::factory()->unverified()->create();

        $this->actingAs($admin)
            ->post(route('platform.impersonate.start', $alvo))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($alvo);

        $this->get(route('verification.notice'))
            ->assertOk()
            ->assertSeeText('Sair do impersonate');

        $this->post(route('platform.impersonate.stop'))
            ->assertRedirect(route('platform.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertSame(0, (int) session('impersonator_id', 0));
        $this->assertSame(0, (int) session('impersonated_user_id', 0));
    }

    public function test_utilizador_normal_nao_acessa_plataforma(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('platform.empresas.index'))
            ->assertForbidden();
    }

    public function test_utilizador_normal_nao_acessa_painel_da_empresa(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('platform.empresas.show', $empresa))
            ->assertForbidden();
    }
}
