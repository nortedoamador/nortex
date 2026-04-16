<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlanosController extends Controller
{
    public function __construct(
        private SubscriptionCheckoutService $checkout,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing('empresa');
        $empresa = $user->empresa;
        abort_unless($empresa && (int) $user->empresa_id === (int) $empresa->id, 403);

        $checkoutBasicaReady = $this->checkout->planConfigured('basica');
        $checkoutCompletaReady = $this->checkout->planConfigured('completa');
        $planoAtivo = $empresa->assinaturaPlataformaAtiva();

        return view('planos.index', [
            'empresa' => $empresa,
            'checkoutBasicaReady' => $checkoutBasicaReady,
            'checkoutCompletaReady' => $checkoutCompletaReady,
            'displayBasicaBrl' => (int) config('services.stripe.plan_basica_display_brl', 297),
            'displayCompletaBrl' => (int) config('services.stripe.plan_completa_display_brl', 497),
            'planoAtivo' => $planoAtivo,
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan' => ['required', 'string', Rule::in(['basica', 'completa'])],
        ]);
        $plan = $validated['plan'];

        $user = $request->user();
        $user->loadMissing('empresa');
        $empresa = $user->empresa;
        if ($empresa === null || (int) $user->empresa_id !== (int) $empresa->id) {
            abort(403);
        }

        if ($empresa->assinaturaPlataformaAtiva()) {
            return redirect()->route('dashboard');
        }

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
