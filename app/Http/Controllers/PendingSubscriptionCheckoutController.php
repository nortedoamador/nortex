<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PendingSubscriptionCheckoutController extends Controller
{
    public function __construct(
        private SubscriptionCheckoutService $checkout,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $user->loadMissing('empresa');
        $empresa = $user->empresa;
        if ($empresa === null || (int) $user->empresa_id !== (int) $empresa->id) {
            abort(403);
        }

        if (! $empresa->pagamento_inicial_pendente) {
            return redirect()->route('dashboard');
        }

        return view('subscription.pagamento-pendente', [
            'empresa' => $empresa,
            'checkoutReady' => $this->checkout->planConfigured('completa'),
        ]);
    }

    public function startCheckout(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->loadMissing('empresa');
        $empresa = $user->empresa;
        if ($empresa === null || (int) $user->empresa_id !== (int) $empresa->id) {
            abort(403);
        }

        if (! $empresa->pagamento_inicial_pendente) {
            return redirect()->route('dashboard');
        }

        if (! $this->checkout->planConfigured('completa')) {
            return redirect()
                ->route('assinatura.pagamento-pendente')
                ->withErrors(['checkout' => __('O pagamento online não está disponível no momento.')]);
        }

        $email = is_string($user->email) ? strtolower(trim($user->email)) : '';

        try {
            $session = $this->checkout->createSubscriptionCheckoutSession(
                $empresa,
                $user,
                'completa',
                $email,
            );
        } catch (\Throwable) {
            return redirect()
                ->route('assinatura.pagamento-pendente')
                ->withErrors(['checkout' => __('Não foi possível iniciar o pagamento. Tente novamente.')]);
        }

        $url = is_string($session->url ?? null) ? $session->url : '';
        if ($url === '') {
            return redirect()
                ->route('assinatura.pagamento-pendente')
                ->withErrors(['checkout' => __('Resposta inválida do serviço de pagamento.')]);
        }

        return redirect()->away($url);
    }
}
