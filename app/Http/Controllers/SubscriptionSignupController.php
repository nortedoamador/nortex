<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionSignupRequest;
use App\Models\Empresa;
use App\Models\User;
use App\Services\EmpresaRbacService;
use App\Services\SubscriptionCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Stripe;

class SubscriptionSignupController extends Controller
{
    public function __construct(
        private EmpresaRbacService $empresaRbac,
        private SubscriptionCheckoutService $checkout,
    ) {}

    public function index(): View
    {
        return view('subscription.index', [
            'basicReady' => $this->checkout->planConfigured('basica'),
            'fullReady' => $this->checkout->planConfigured('completa'),
            'displayBasicaBrl' => (int) config('services.stripe.plan_basica_display_brl', 297),
            'displayCompletaBrl' => (int) config('services.stripe.plan_completa_display_brl', 497),
        ]);
    }

    public function create(string $plan): View|RedirectResponse
    {
        $plan = strtolower($plan);
        if (! in_array($plan, ['basica', 'completa'], true)) {
            abort(404);
        }

        if (! $this->checkout->planConfigured($plan)) {
            return redirect()
                ->route('assinatura.index')
                ->withErrors(['plan' => __('Este plano não está disponível no momento.')]);
        }

        return view('subscription.form', [
            'plan' => $plan,
            'planLabel' => $plan === 'completa' ? __('Completo (com financeiro)') : __('Essencial (sem financeiro)'),
            'planPrice' => (string) ($plan === 'completa'
                ? config('services.stripe.plan_completa_display_brl', 497)
                : config('services.stripe.plan_basica_display_brl', 297)),
        ]);
    }

    public function store(SubscriptionSignupRequest $request, string $plan): RedirectResponse
    {
        $plan = strtolower($plan);
        if (! in_array($plan, ['basica', 'completa'], true)) {
            abort(404);
        }

        if (! $this->checkout->planConfigured($plan)) {
            return redirect()
                ->route('assinatura.index')
                ->withErrors(['plan' => __('Este plano não está disponível no momento.')]);
        }

        $data = $request->validated();
        $email = Str::lower(trim($data['email']));

        try {
            $checkoutUrl = DB::transaction(function () use ($data, $email, $plan): string {
                $slug = $this->uniqueSlugFromCompanyName($data['nome_empresa']);

                $empresa = Empresa::query()->create([
                    'nome' => $data['nome_empresa'],
                    'slug' => $slug,
                    'ativo' => true,
                    'email_contato' => $email,
                    'telefone' => $data['telefone'],
                ]);

                $this->empresaRbac->bootstrapEmpresa($empresa);

                $user = User::query()->create([
                    'empresa_id' => $empresa->id,
                    'name' => $data['nome_responsavel'],
                    'email' => $email,
                    'password' => Str::password(48),
                    'is_disabled' => true,
                ]);

                $this->empresaRbac->assignRole($user, 'administrador');

                $session = $this->checkout->createSubscriptionCheckoutSession(
                    $empresa,
                    $user,
                    $plan,
                    $email,
                );

                return $session->url ?? '';
            });
        } catch (\Throwable $e) {
            Log::error('Assinatura: falha ao iniciar checkout.', [
                'plan' => $plan,
                'email' => $email,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['checkout' => __('Não foi possível iniciar o pagamento. Tente novamente ou contacte o suporte.')]);
        }

        if ($checkoutUrl === '') {
            return back()
                ->withInput()
                ->withErrors(['checkout' => __('Resposta inválida do serviço de pagamento.')]);
        }

        return redirect()->away($checkoutUrl);
    }

    public function success(Request $request): View
    {
        $sessionId = $request->query('session_id');
        $email = null;
        $paid = false;

        if (is_string($sessionId) && $sessionId !== '' && config('services.stripe.secret')) {
            try {
                Stripe::setApiKey((string) config('services.stripe.secret'));
                $session = StripeCheckoutSession::retrieve($sessionId);
                $paid = ($session->payment_status ?? '') === 'paid';
                $email = $session->customer_email ?? null;
                if (! is_string($email) && isset($session->customer_details) && is_object($session->customer_details)) {
                    $detailsEmail = $session->customer_details->email ?? null;
                    $email = is_string($detailsEmail) ? $detailsEmail : null;
                }
            } catch (\Throwable) {
                $paid = false;
            }
        }

        return view('subscription.success', [
            'sessionId' => is_string($sessionId) ? $sessionId : null,
            'paid' => $paid,
            'email' => is_string($email) ? $email : null,
        ]);
    }

    public function canceled(Request $request): RedirectResponse|View
    {
        $plan = $request->query('plan');
        if (is_string($plan) && in_array(strtolower($plan), ['basica', 'completa'], true)) {
            return redirect()
                ->route('assinatura.create', ['plan' => strtolower($plan)])
                ->with('status', __('Pagamento cancelado. Pode tentar novamente quando quiser.'));
        }

        return redirect()->route('assinatura.index');
    }

    private function uniqueSlugFromCompanyName(string $name): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'empresa';
        }

        $slug = $base;
        $i = 0;
        while (Empresa::query()->where('slug', $slug)->exists()) {
            $i++;
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
