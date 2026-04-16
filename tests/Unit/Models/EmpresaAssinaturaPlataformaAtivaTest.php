<?php

namespace Tests\Unit\Models;

use App\Models\Empresa;
use Illuminate\Support\Facades\Config;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class EmpresaAssinaturaPlataformaAtivaTest extends TestCase
{
    use SafeRefreshDatabase;

    private function makeEmpresa(array $attributes = []): Empresa
    {
        return Empresa::factory()->create(array_merge([
            'pagamento_inicial_pendente' => false,
        ], $attributes));
    }

    public function test_pagamento_inicial_pendente_bloqueia_mesmo_com_subscrição(): void
    {
        Config::set('services.stripe.price_full', 'price_full');
        Config::set('services.stripe.price_basic', '');
        $empresa = $this->makeEmpresa([
            'pagamento_inicial_pendente' => true,
            'stripe_customer_id' => 'cus_1',
            'stripe_subscription_id' => 'sub_1',
            'stripe_subscription_status' => 'active',
            'stripe_current_price_id' => 'price_full',
        ]);

        $this->assertFalse($empresa->assinaturaPlataformaAtiva());
    }

    public function test_sem_price_full_sem_stripe_e_sem_enforce_permite_legado(): void
    {
        Config::set('services.stripe.price_full', '');
        Config::set('services.stripe.price_basic', '');
        Config::set('services.stripe.enforce_subscription', false);
        $empresa = $this->makeEmpresa();

        $this->assertTrue($empresa->assinaturaPlataformaAtiva());
    }

    public function test_com_price_full_sem_stripe_retorna_false(): void
    {
        Config::set('services.stripe.price_full', 'price_abc');
        Config::set('services.stripe.price_basic', '');
        Config::set('services.stripe.enforce_subscription', false);
        $empresa = $this->makeEmpresa();

        $this->assertFalse($empresa->assinaturaPlataformaAtiva());
    }

    public function test_stripe_active_com_price_correto_retorna_true(): void
    {
        Config::set('services.stripe.price_full', 'price_abc');
        Config::set('services.stripe.price_basic', '');
        $empresa = $this->makeEmpresa([
            'stripe_customer_id' => 'cus_1',
            'stripe_subscription_id' => 'sub_1',
            'stripe_subscription_status' => 'active',
            'stripe_current_price_id' => 'price_abc',
        ]);

        $this->assertTrue($empresa->assinaturaPlataformaAtiva());
    }

    public function test_stripe_trialing_com_price_correto_retorna_true(): void
    {
        Config::set('services.stripe.price_full', 'price_abc');
        Config::set('services.stripe.price_basic', '');
        $empresa = $this->makeEmpresa([
            'stripe_customer_id' => 'cus_1',
            'stripe_subscription_id' => 'sub_1',
            'stripe_subscription_status' => 'trialing',
            'stripe_current_price_id' => 'price_abc',
        ]);

        $this->assertTrue($empresa->assinaturaPlataformaAtiva());
    }

    public function test_stripe_active_com_price_errado_retorna_false(): void
    {
        Config::set('services.stripe.price_full', 'price_abc');
        Config::set('services.stripe.price_basic', '');
        $empresa = $this->makeEmpresa([
            'stripe_customer_id' => 'cus_1',
            'stripe_subscription_id' => 'sub_1',
            'stripe_subscription_status' => 'active',
            'stripe_current_price_id' => 'price_other',
        ]);

        $this->assertFalse($empresa->assinaturaPlataformaAtiva());
    }

    public function test_stripe_past_due_retorna_false(): void
    {
        Config::set('services.stripe.price_full', 'price_abc');
        Config::set('services.stripe.price_basic', '');
        $empresa = $this->makeEmpresa([
            'stripe_customer_id' => 'cus_1',
            'stripe_subscription_id' => 'sub_1',
            'stripe_subscription_status' => 'past_due',
            'stripe_current_price_id' => 'price_abc',
        ]);

        $this->assertFalse($empresa->assinaturaPlataformaAtiva());
    }

    public function test_sem_price_full_com_stripe_active_retorna_true_independentemente_do_price_id(): void
    {
        Config::set('services.stripe.price_full', '');
        Config::set('services.stripe.price_basic', '');
        $empresa = $this->makeEmpresa([
            'stripe_customer_id' => 'cus_1',
            'stripe_subscription_id' => 'sub_1',
            'stripe_subscription_status' => 'active',
            'stripe_current_price_id' => 'price_qualquer',
        ]);

        $this->assertTrue($empresa->assinaturaPlataformaAtiva());
    }

    public function test_so_price_basic_sem_stripe_retorna_false(): void
    {
        Config::set('services.stripe.price_full', '');
        Config::set('services.stripe.price_basic', 'price_bas');
        Config::set('services.stripe.enforce_subscription', false);
        $empresa = $this->makeEmpresa();

        $this->assertFalse($empresa->assinaturaPlataformaAtiva());
    }

    public function test_so_price_basic_com_stripe_basico_retorna_true(): void
    {
        Config::set('services.stripe.price_full', '');
        Config::set('services.stripe.price_basic', 'price_bas');
        $empresa = $this->makeEmpresa([
            'stripe_customer_id' => 'cus_1',
            'stripe_subscription_id' => 'sub_1',
            'stripe_subscription_status' => 'active',
            'stripe_current_price_id' => 'price_bas',
        ]);

        $this->assertTrue($empresa->assinaturaPlataformaAtiva());
    }

    public function test_price_full_e_basic_aceita_subscrição_basica(): void
    {
        Config::set('services.stripe.price_full', 'price_full_x');
        Config::set('services.stripe.price_basic', 'price_bas_x');
        $empresa = $this->makeEmpresa([
            'stripe_customer_id' => 'cus_1',
            'stripe_subscription_id' => 'sub_1',
            'stripe_subscription_status' => 'active',
            'stripe_current_price_id' => 'price_bas_x',
        ]);

        $this->assertTrue($empresa->assinaturaPlataformaAtiva());
    }

    public function test_price_full_e_basic_rejeita_outro_price_id(): void
    {
        Config::set('services.stripe.price_full', 'price_full_x');
        Config::set('services.stripe.price_basic', 'price_bas_x');
        $empresa = $this->makeEmpresa([
            'stripe_customer_id' => 'cus_1',
            'stripe_subscription_id' => 'sub_1',
            'stripe_subscription_status' => 'active',
            'stripe_current_price_id' => 'price_outro',
        ]);

        $this->assertFalse($empresa->assinaturaPlataformaAtiva());
    }

    public function test_plano_basico_nao_inclui_financeiro_quando_full_configurado(): void
    {
        Config::set('services.stripe.price_full', 'price_full_x');
        Config::set('services.stripe.price_basic', 'price_bas_x');
        $empresa = $this->makeEmpresa([
            'stripe_customer_id' => 'cus_1',
            'stripe_subscription_id' => 'sub_1',
            'stripe_subscription_status' => 'active',
            'stripe_current_price_id' => 'price_bas_x',
        ]);

        $this->assertTrue($empresa->assinaturaPlataformaAtiva());
        $this->assertFalse($empresa->billingIncludesFinanceiro());
    }
}
