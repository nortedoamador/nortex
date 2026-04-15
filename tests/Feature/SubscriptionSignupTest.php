<?php

namespace Tests\Feature;

use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class SubscriptionSignupTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_assinatura_index_returns_ok(): void
    {
        $this->get(route('assinatura.index'))->assertOk();
    }

    public function test_assinatura_create_redirects_when_price_not_configured(): void
    {
        config([
            'services.stripe.price_basic' => null,
            'services.stripe.price_full' => null,
        ]);

        $this->get(route('assinatura.create', ['plan' => 'basica']))
            ->assertRedirect(route('assinatura.index'))
            ->assertSessionHasErrors('plan');
    }

    public function test_assinatura_create_shows_form_when_prices_configured(): void
    {
        config([
            'services.stripe.price_basic' => 'price_test_basic',
            'services.stripe.price_full' => 'price_test_full',
        ]);

        $this->get(route('assinatura.create', ['plan' => 'completa']))
            ->assertOk()
            ->assertSee('Completo', false);
    }
}
