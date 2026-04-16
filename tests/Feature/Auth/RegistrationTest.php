<?php

namespace Tests\Feature\Auth;

use App\Services\SubscriptionCheckoutService;
use Illuminate\Support\Facades\Config;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_register_redireciona_para_checkout_quando_enforce_e_stripe_configurados(): void
    {
        Config::set('services.stripe.enforce_subscription', true);
        Config::set('services.stripe.secret', 'sk_test_fake');
        Config::set('services.stripe.price_full', 'price_test_full');
        Config::set('services.stripe.price_basic', '');

        $checkoutUrl = 'https://checkout.stripe.test/session-registo';

        $session = StripeCheckoutSession::constructFrom([
            'id' => 'cs_test_registo',
            'object' => 'checkout.session',
            'url' => $checkoutUrl,
        ]);

        $mock = $this->mock(SubscriptionCheckoutService::class);
        $mock->shouldReceive('planConfigured')->with('completa')->andReturn(true);
        $mock->shouldReceive('createSubscriptionCheckoutSession')->once()->andReturn($session);

        $response = $this->post('/register', [
            'empresa_nome' => 'Empresa Teste',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect($checkoutUrl);

        $this->assertDatabaseHas('empresas', [
            'nome' => 'Empresa Teste',
            'pagamento_inicial_pendente' => true,
        ]);
    }

    public function test_register_falha_quando_enforce_true_sem_stripe_completo(): void
    {
        Config::set('services.stripe.enforce_subscription', true);
        Config::set('services.stripe.secret', '');
        Config::set('services.stripe.price_full', '');
        Config::set('services.stripe.price_basic', '');

        $response = $this->post('/register', [
            'empresa_nome' => 'Empresa X',
            'name' => 'User X',
            'email' => 'userx@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('empresa_nome');
    }

    public function test_register_vai_ao_dashboard_sem_stripe_quando_enforce_false(): void
    {
        Config::set('services.stripe.enforce_subscription', false);
        Config::set('services.stripe.secret', '');
        Config::set('services.stripe.price_full', '');
        Config::set('services.stripe.price_basic', '');

        $response = $this->post('/register', [
            'empresa_nome' => 'Empresa Local',
            'name' => 'User Local',
            'email' => 'local@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('empresas', [
            'nome' => 'Empresa Local',
            'pagamento_inicial_pendente' => false,
        ]);
    }

    public function test_register_vai_ao_dashboard_com_price_full_quando_enforce_false(): void
    {
        Config::set('services.stripe.enforce_subscription', false);
        Config::set('services.stripe.secret', 'sk_test_fake');
        Config::set('services.stripe.price_full', 'price_test_full');
        Config::set('services.stripe.price_basic', '');

        $response = $this->post('/register', [
            'empresa_nome' => 'Empresa Dashboard Primeiro',
            'name' => 'User Dash',
            'email' => 'dashfirst@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('empresas', [
            'nome' => 'Empresa Dashboard Primeiro',
            'pagamento_inicial_pendente' => false,
        ]);
    }
}
