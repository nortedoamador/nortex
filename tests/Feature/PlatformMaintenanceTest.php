<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use App\Support\PlatformMaintenance;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class PlatformMaintenanceTest extends TestCase
{
    use SafeRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget(PlatformMaintenance::CACHE_KEY);
    }

    public function test_utilizador_empresa_recebe_503_quando_manutencao_ativa(): void
    {
        PlatformMaintenance::enable();
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertStatus(503)
            ->assertSeeText(__('Manutenção'));
    }

    public function test_admin_plataforma_acede_com_manutencao_ativa(): void
    {
        PlatformMaintenance::enable();
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->get(route('platform.dashboard'))
            ->assertOk();
    }

    public function test_login_disponivel_com_manutencao_ativa(): void
    {
        PlatformMaintenance::enable();

        $this->get(route('login'))
            ->assertOk();
    }

    public function test_admin_plataforma_pode_alternar_manutencao(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->post(route('platform.maintenance.update'), ['enabled' => '1'])
            ->assertRedirect();

        $this->assertTrue(PlatformMaintenance::enabled());

        $this->actingAs($admin)
            ->post(route('platform.maintenance.update'), ['enabled' => '0'])
            ->assertRedirect();

        $this->assertFalse(PlatformMaintenance::enabled());
    }
}
