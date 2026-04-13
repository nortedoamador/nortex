<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
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

    public function test_utilizador_normal_nao_acessa_plataforma(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('platform.empresas.index'))
            ->assertForbidden();
    }
}
