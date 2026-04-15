<?php

namespace Tests\Feature;

use Tests\Concerns\SafeRefreshDatabase;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_webhook_returns_503_when_secret_missing(): void
    {
        config(['services.stripe.webhook_secret' => null]);

        $response = $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_Stripe-Signature' => 't=1,v1=abc',
            'CONTENT_TYPE' => 'application/json',
        ], '{}');

        $response->assertStatus(503);
    }

    public function test_webhook_returns_400_on_invalid_signature(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test_secret_for_invalid_payload']);

        $response = $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_Stripe-Signature' => 't=0,v1=deadbeef',
            'CONTENT_TYPE' => 'application/json',
        ], '{}');

        $response->assertStatus(400);
    }
}
