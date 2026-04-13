<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class PlatformCadastrosTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_platform_admin_acessa_cadastros_globais(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->get(route('platform.cadastros.tipos-processo.index'))
            ->assertOk();
    }

    public function test_utilizador_normal_nao_acessa_cadastros_globais(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('platform.cadastros.tipos-processo.index'))
            ->assertForbidden();
    }
}

