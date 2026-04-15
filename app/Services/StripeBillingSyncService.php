<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Stripe\Checkout\Session;
use Stripe\Invoice;
use Stripe\Stripe;
use Stripe\Subscription;

class StripeBillingSyncService
{
    public function __construct()
    {
        $secret = config('services.stripe.secret');
        if (is_string($secret) && $secret !== '') {
            Stripe::setApiKey($secret);
        }
    }

    public function syncFromCheckoutSession(Session $session): void
    {
        if ($session->mode !== 'subscription') {
            return;
        }

        $empresaId = $session->metadata['empresa_id'] ?? null;
        if ($empresaId !== null && $empresaId !== '') {
            $empresa = Empresa::query()->whereKey($empresaId)->first();
            if ($empresa && is_string($session->customer) && $session->customer !== '') {
                $empresa->stripe_customer_id = $session->customer;
                $empresa->save();
            }
        }

        $subscriptionId = is_string($session->subscription) ? $session->subscription : null;
        if ($subscriptionId) {
            $this->syncFromSubscriptionId($subscriptionId);
        }

        $this->activateOwnerAfterCheckout($session);
    }

    /**
     * Ativa o utilizador administrador criado no fluxo público de assinatura e envia o e-mail para definir senha.
     */
    private function activateOwnerAfterCheckout(Session $session): void
    {
        if ($session->mode !== 'subscription' || ($session->payment_status ?? '') !== 'paid') {
            return;
        }

        $userId = $session->metadata['user_id'] ?? null;
        if ($userId === null || $userId === '') {
            return;
        }

        $user = User::query()->whereKey($userId)->first();
        if (! $user || $user->is_platform_admin) {
            return;
        }

        $wasDisabled = (bool) $user->is_disabled;
        $user->forceFill(['is_disabled' => false])->save();

        if ($user->empresa_id) {
            Empresa::query()->whereKey($user->empresa_id)->update(['pagamento_inicial_pendente' => false]);
        }

        if ($wasDisabled) {
            $mailStatus = Password::sendResetLink(['email' => $user->email]);
            if ($mailStatus !== Password::RESET_LINK_SENT) {
                Log::warning('Stripe checkout: conta ativada mas falha ao enviar link de senha.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'password_broker_status' => $mailStatus,
                ]);
            }
        }
    }

    public function syncFromSubscriptionId(?string $subscriptionId): void
    {
        if (! is_string($subscriptionId) || $subscriptionId === '') {
            return;
        }

        if (! config('services.stripe.secret')) {
            Log::warning('StripeBillingSyncService: STRIPE_SECRET não configurado.');

            return;
        }

        $subscription = Subscription::retrieve(
            $subscriptionId,
            ['expand' => ['items.data.price']]
        );

        $customerId = is_string($subscription->customer) ? $subscription->customer : null;
        if (! $customerId) {
            Log::warning('StripeBillingSyncService: subscrição sem customer.', ['subscription' => $subscriptionId]);

            return;
        }

        $empresa = Empresa::query()->where('stripe_customer_id', $customerId)->first();

        if (! $empresa) {
            $metaEmpresaId = $subscription->metadata['empresa_id'] ?? null;
            if ($metaEmpresaId !== null && $metaEmpresaId !== '') {
                $empresa = Empresa::query()->whereKey($metaEmpresaId)->first();
            }
        }

        if (! $empresa) {
            Log::warning('StripeBillingSyncService: empresa não encontrada para customer.', [
                'customer' => $customerId,
                'subscription' => $subscriptionId,
            ]);

            return;
        }

        $priceId = null;
        $items = $subscription->items->data ?? [];
        if ($items !== [] && isset($items[0]->price)) {
            $price = $items[0]->price;
            $priceId = is_string($price) ? $price : ($price->id ?? null);
        }

        $attrs = [
            'stripe_customer_id' => $customerId,
            'stripe_subscription_id' => $subscription->id,
            'stripe_subscription_status' => $subscription->status,
            'stripe_current_price_id' => $priceId,
            'stripe_subscription_cancel_at_period_end' => (bool) ($subscription->cancel_at_period_end ?? false),
        ];

        if (in_array($subscription->status, ['active', 'trialing'], true)) {
            $attrs['pagamento_inicial_pendente'] = false;
        }

        $empresa->forceFill($attrs)->save();
    }

    public function syncFromInvoice(Invoice $invoice): void
    {
        $subscriptionId = is_string($invoice->subscription) ? $invoice->subscription : null;
        if ($subscriptionId) {
            $this->syncFromSubscriptionId($subscriptionId);
        }
    }

    public function clearSubscriptionForCustomer(string $customerId, ?string $subscriptionId = null): void
    {
        $query = Empresa::query()->where('stripe_customer_id', $customerId);
        if (is_string($subscriptionId) && $subscriptionId !== '') {
            $query->where('stripe_subscription_id', $subscriptionId);
        }

        $query->update([
            'stripe_subscription_id' => null,
            'stripe_subscription_status' => 'canceled',
            'stripe_current_price_id' => null,
            'stripe_subscription_cancel_at_period_end' => false,
        ]);
    }

    /**
     * Atualiza os campos Stripe da empresa a partir da API (subscrição atual ou primeira ativa do cliente).
     */
    public function syncFromStripeForEmpresa(Empresa $empresa): bool
    {
        if (! config('services.stripe.secret')) {
            return false;
        }

        if (is_string($empresa->stripe_subscription_id) && $empresa->stripe_subscription_id !== '') {
            $this->syncFromSubscriptionId($empresa->stripe_subscription_id);

            return true;
        }

        $customerId = $empresa->stripe_customer_id;
        if (! is_string($customerId) || $customerId === '') {
            return false;
        }

        $list = Subscription::all([
            'customer' => $customerId,
            'limit' => 20,
        ]);

        foreach ($list->data as $sub) {
            if (in_array($sub->status, ['active', 'trialing', 'past_due'], true)) {
                $this->syncFromSubscriptionId($sub->id);

                return true;
            }
        }

        if ($list->data !== []) {
            $this->syncFromSubscriptionId($list->data[0]->id);

            return true;
        }

        return false;
    }

    /**
     * Marca ou desmarca cancelamento da subscrição no fim do período de faturação (Stripe).
     *
     * @throws \RuntimeException
     */
    public function setSubscriptionCancelAtPeriodEnd(Empresa $empresa, bool $cancelAtPeriodEnd): void
    {
        if (! config('services.stripe.secret')) {
            throw new \RuntimeException(__('Stripe não está configurado.'));
        }

        $subId = $empresa->stripe_subscription_id;
        if (! is_string($subId) || $subId === '') {
            throw new \RuntimeException(__('Esta empresa não tem subscrição Stripe.'));
        }

        Subscription::update($subId, [
            'cancel_at_period_end' => $cancelAtPeriodEnd,
        ]);

        $this->syncFromSubscriptionId($subId);
    }
}
