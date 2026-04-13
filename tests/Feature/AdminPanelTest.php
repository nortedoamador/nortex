<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_operador_nao_acessa_papeis_na_plataforma(): void
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
            ->get(route('platform.empresas.admin.roles.index', $empresa))
            ->assertForbidden();
    }

    public function test_administrador_da_empresa_nao_acessa_papeis_pela_plataforma(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($admin)
            ->get(route('platform.empresas.admin.roles.index', $empresa))
            ->assertForbidden();
    }

    public function test_admin_plataforma_acessa_papeis_e_relatorios_por_empresa(): void
    {
        $empresa = Empresa::factory()->create();
        $platform = User::factory()->platformAdmin()->create();

        $this->actingAs($platform)
            ->get(route('platform.empresas.admin.roles.index', $empresa))
            ->assertOk();

        $this->actingAs($platform)
            ->get(route('platform.empresas.admin.relatorios.index', $empresa))
            ->assertOk();

        $manage = Permission::query()->where('slug', 'cadastros.manage')->first();
        $this->assertNotNull($manage);
    }
}
