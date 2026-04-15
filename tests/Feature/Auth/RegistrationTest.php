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

    public function test_register_redireciona_para_checkout_quando_stripe_completo_configurado(): void
    {
        Config::set('services.stripe.secret', 'sk_test_fake');
        Config::set('services.stripe.price_full', 'price_test_full');

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

    public function test_register_falha_quando_plano_completo_nao_configurado(): void
    {
        Config::set('services.stripe.secret', '');
        Config::set('services.stripe.price_full', '');

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
}
