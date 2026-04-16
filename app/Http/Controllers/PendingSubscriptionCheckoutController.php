<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PendingSubscriptionCheckoutController extends Controller
{
    public function __construct(
        private SubscriptionCheckoutService $checkout,
    ) {}

    public function show(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->loadMissing('empresa');
        $empresa = $user->empresa;
        if ($empresa === null || (int) $user->empresa_id !== (int) $empresa->id) {
            abort(403);
        }

        return redirect()->route('planos.index');
    }

    public function startCheckout(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->loadMissing('empresa');
        $empresa = $user->empresa;
        if ($empresa === null || (int) $user->empresa_id !== (int) $empresa->id) {
            abort(403);
        }

        $validated = $request->validate([
            'plan' => ['sometimes', 'string', Rule::in(['basica', 'completa'])],
        ]);
        $plan = $validated['plan'] ?? 'completa';

        if (! $this->checkout->planConfigured($plan)) {
            return redirect()
                ->route('planos.index')
                ->withErrors(['checkout' => __('O pagamento online não está disponível no momento.')]);
        }

        $email = is_string($user->email) ? strtolower(trim($user->email)) : '';

        $successUrl = route('dashboard', [], true).'?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('planos.index', [], true);

        try {
            $session = $this->checkout->createSubscriptionCheckoutSession(
                $empresa,
                $user,
                $plan,
                $email,
                $successUrl,
                $cancelUrl,
            );
        } catch (\Throwable) {
            return redirect()
                ->route('planos.index')
                ->withErrors(['checkout' => __('Não foi possível iniciar o pagamento. Tente novamente.')]);
        }

        $url = is_string($session->url ?? null) ? $session->url : '';
        if ($url === '') {
            return redirect()
                ->route('planos.index')
                ->withErrors(['checkout' => __('Resposta inválida do serviço de pagamento.')]);
        }

        return redirect()->away($url);
    }
}
