<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class SubscriptionCheckoutService
{
    public function __construct()
    {
        $secret = config('services.stripe.secret');
        if (is_string($secret) && $secret !== '') {
            Stripe::setApiKey($secret);
        }
    }

    /**
     * @throws ApiErrorException
     */
    public function createSubscriptionCheckoutSession(
        Empresa $empresa,
        User $owner,
        string $planSlug,
        string $customerEmail,
    ): Session {
        $priceId = $this->priceIdForPlan($planSlug);

        $successUrl = route('assinatura.sucesso', [], true).'?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('assinatura.cancelado', [], true).'?plan='.rawurlencode($planSlug);

        return Session::create([
            'mode' => 'subscription',
            'locale' => 'pt-BR',
            'customer_email' => $customerEmail,
            'line_items' => [
                [
                    'price' => $priceId,
                    'quantity' => 1,
                ],
            ],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $empresa->id,
            'metadata' => [
                'empresa_id' => (string) $empresa->id,
                'user_id' => (string) $owner->id,
                'plan' => $planSlug,
            ],
            'subscription_data' => [
                'metadata' => [
                    'empresa_id' => (string) $empresa->id,
                ],
            ],
        ]);
    }

    public function priceIdForPlan(string $planSlug): string
    {
        $planSlug = strtolower($planSlug);
        $id = match ($planSlug) {
            'basica' => config('services.stripe.price_basic'),
            'completa' => config('services.stripe.price_full'),
            default => null,
        };

        if (! is_string($id) || $id === '') {
            Log::error('SubscriptionCheckoutService: Price ID em falta para o plano.', ['plan' => $planSlug]);

            throw new \RuntimeException(__('Configuração de pagamento incompleta. Contacte o suporte.'));
        }

        return $id;
    }

    public function planConfigured(string $planSlug): bool
    {
        try {
            $this->priceIdForPlan($planSlug);

            return true;
        } catch (\RuntimeException) {
            return false;
        }
    }
}
