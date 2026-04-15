<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class EmpresaAcessoPlataformaTest extends TestCase
{
    use SafeRefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_tenant_pode_aceder_no_ultimo_dia_do_limite(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-10 15:00:00', config('app.timezone')));
        $empresa = Empresa::factory()->create([
            'email_contato' => 'e@acesso.test',
            'acesso_plataforma_ate' => '2026-04-10',
        ]);
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    public function test_tenant_bloqueado_apos_data_limite_redireciona_para_login(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-11 08:00:00', config('app.timezone')));
        $empresa = Empresa::factory()->create([
            'email_contato' => 'e@acesso.test',
            'acesso_plataforma_ate' => '2026-04-10',
        ]);
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_sem_data_limite_permite_acesso(): void
    {
        Carbon::setTestNow(Carbon::parse('2099-01-01 12:00:00', config('app.timezone')));
        $empresa = Empresa::factory()->create([
            'email_contato' => 'e@acesso.test',
            'acesso_plataforma_ate' => null,
        ]);
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }
}
