<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class PlatformAssinaturasTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_platform_admin_acessa_assinaturas(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        Empresa::factory()->create([
            'stripe_customer_id' => 'cus_test_1',
            'stripe_subscription_id' => 'sub_test_1',
            'stripe_subscription_status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('platform.assinaturas.index'))
            ->assertOk()
            ->assertSeeText('Assinaturas Stripe');
    }

    public function test_rota_manual_antiga_redireciona_para_index(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $empresa = Empresa::factory()->create();

        $this->actingAs($admin)
            ->get(route('platform.assinaturas.manual', ['empresa_id' => $empresa->id]))
            ->assertRedirect(route('platform.assinaturas.index', ['empresa_id' => $empresa->id]));
    }

    public function test_platform_admin_ve_formulario_manual_na_lista(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $empresa = Empresa::factory()->create();

        $this->actingAs($admin)
            ->get(route('platform.assinaturas.index', ['empresa_id' => $empresa->id]))
            ->assertOk()
            ->assertSeeText('Guardar dados Stripe', false)
            ->assertSeeText($empresa->nome, false);
    }

    public function test_utilizador_normal_nao_acessa_assinaturas(): void
    {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('platform.assinaturas.index'))
            ->assertForbidden();
    }

    public function test_platform_admin_acessa_formulario_adicionar_empresa(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->get(route('platform.assinaturas.adicionar'))
            ->assertOk()
            ->assertSeeText('Criar empresa e guardar', false);
    }

    public function test_platform_admin_cria_empresa_com_assinatura_na_area_assinaturas(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->post(route('platform.assinaturas.adicionar.store'), [
                'nome' => 'Empresa Teste Sub',
                'email_contato' => 'contato@empresa-sub.test',
                'telefone' => '11999990000',
                'admin_name' => 'Admin Sub',
                'admin_email' => 'admin@empresa-sub.test',
                'stripe_customer_id' => 'cus_adicionar_test',
                'stripe_subscription_id' => 'sub_adicionar_test',
                'stripe_subscription_status' => 'active',
                'stripe_current_price_id' => 'price_adicionar_test',
                'acesso_plataforma_ate' => '2030-06-01',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('empresas', [
            'nome' => 'Empresa Teste Sub',
            'slug' => 'empresa-teste-sub',
            'email_contato' => 'contato@empresa-sub.test',
            'stripe_customer_id' => 'cus_adicionar_test',
        ]);
        $criada = Empresa::query()->where('slug', 'empresa-teste-sub')->first();
        $this->assertNotNull($criada);
        $this->assertSame('2030-06-01', $criada->acesso_plataforma_ate?->format('Y-m-d'));

        $this->assertDatabaseHas('users', [
            'email' => 'admin@empresa-sub.test',
            'name' => 'Admin Sub',
        ]);
    }

    public function test_platform_admin_guarda_cadastro_manual(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $empresa = Empresa::factory()->create();

        $this->actingAs($admin)
            ->post(route('platform.assinaturas.manual.store'), [
                'empresa_id' => $empresa->id,
                'stripe_customer_id' => 'cus_test_manual',
                'stripe_subscription_id' => 'sub_test_manual',
                'stripe_subscription_status' => 'active',
                'stripe_current_price_id' => 'price_test_manual',
            ])
            ->assertRedirect(route('platform.assinaturas.index', ['empresa_id' => $empresa->id]));

        $empresa->refresh();
        $this->assertSame('cus_test_manual', $empresa->stripe_customer_id);
        $this->assertSame('sub_test_manual', $empresa->stripe_subscription_id);
        $this->assertSame('active', $empresa->stripe_subscription_status);
        $this->assertSame('price_test_manual', $empresa->stripe_current_price_id);
        $this->assertFalse($empresa->stripe_subscription_cancel_at_period_end);
    }

    public function test_platform_admin_guarda_data_limite_acesso_no_formulario_manual(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $empresa = Empresa::factory()->create(['acesso_plataforma_ate' => null]);

        $this->actingAs($admin)
            ->post(route('platform.assinaturas.manual.store'), [
                'empresa_id' => $empresa->id,
                'acesso_plataforma_ate' => '2028-12-31',
                'stripe_customer_id' => null,
                'stripe_subscription_id' => null,
                'stripe_subscription_status' => null,
                'stripe_current_price_id' => null,
            ])
            ->assertRedirect(route('platform.assinaturas.index', ['empresa_id' => $empresa->id]));

        $empresa->refresh();
        $this->assertSame('2028-12-31', $empresa->acesso_plataforma_ate?->format('Y-m-d'));
    }
}
