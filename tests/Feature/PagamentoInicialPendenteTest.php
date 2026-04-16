<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class PagamentoInicialPendenteTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_dashboard_acessivel_com_pagamento_pendente_exibe_bloqueio(): void
    {
        $empresa = Empresa::factory()->create([
            'email_contato' => 'c@pendente.test',
            'pagamento_inicial_pendente' => true,
        ]);
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText(__('Sem plano ativo'), false);
    }

    public function test_utilizador_sem_pendente_acessa_dashboard(): void
    {
        $empresa = Empresa::factory()->create([
            'email_contato' => 'c@ok.test',
            'pagamento_inicial_pendente' => false,
        ]);
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_modulos_tenant_redirecionam_sem_plano_ativo(): void
    {
        $empresa = Empresa::factory()->create([
            'email_contato' => 'c@bloqueado.test',
            'pagamento_inicial_pendente' => true,
        ]);
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('clientes.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_dashboard_exibe_bloqueio_sem_stripe_quando_price_full_configurado(): void
    {
        Config::set('services.stripe.price_full', 'price_test_full');
        Config::set('services.stripe.price_basic', '');
        Config::set('services.stripe.enforce_subscription', false);

        $empresa = Empresa::factory()->create([
            'email_contato' => 'c@nostripe.test',
            'pagamento_inicial_pendente' => false,
        ]);
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText(__('Sem plano ativo'), false);
    }

    public function test_modulos_tenant_redirecionam_sem_stripe_quando_price_full_configurado(): void
    {
        Config::set('services.stripe.price_full', 'price_test_full');
        Config::set('services.stripe.price_basic', '');
        Config::set('services.stripe.enforce_subscription', false);

        $empresa = Empresa::factory()->create([
            'email_contato' => 'c@modulos.test',
            'pagamento_inicial_pendente' => false,
        ]);
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('clientes.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_dashboard_exibe_bloqueio_sem_stripe_quando_so_price_basic_configurado(): void
    {
        Config::set('services.stripe.price_full', '');
        Config::set('services.stripe.price_basic', 'price_test_basic');
        Config::set('services.stripe.enforce_subscription', false);

        $empresa = Empresa::factory()->create([
            'email_contato' => 'c@sobasic.test',
            'pagamento_inicial_pendente' => false,
        ]);
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText(__('Sem plano ativo'), false);
    }

    public function test_modulos_tenant_redirecionam_sem_stripe_quando_so_price_basic_configurado(): void
    {
        Config::set('services.stripe.price_full', '');
        Config::set('services.stripe.price_basic', 'price_test_basic');
        Config::set('services.stripe.enforce_subscription', false);

        $empresa = Empresa::factory()->create([
            'email_contato' => 'c@modulosbasic.test',
            'pagamento_inicial_pendente' => false,
        ]);
        $user = User::factory()->create(['empresa_id' => $empresa->id]);

        $this->actingAs($user)
            ->get(route('clientes.index'))
            ->assertRedirect(route('dashboard'));
    }
}
