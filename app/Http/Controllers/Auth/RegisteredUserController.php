<?php

namespace App\Http\Controllers\Auth;

use App\Http\Concerns\RespondsForNorteXAuthSpa;
use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use App\Services\EmpresaProcessosDefaultsService;
use App\Services\EmpresaRbacService;
use App\Services\SubscriptionCheckoutService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    use RespondsForNorteXAuthSpa;

    public function __construct(
        private SubscriptionCheckoutService $checkout,
    ) {}

    /**
     * Display the registration view.
     */
    public function create(Request $request): View|JsonResponse
    {
        if ($this->nxAuthSpa($request)) {
            return $this->nxAuthSpaFragment('auth.partials.register-inner', [], 'Criar conta');
        }

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'empresa_nome' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (! $this->checkout->planConfigured('completa')) {
            throw ValidationException::withMessages([
                'empresa_nome' => __('O pagamento do plano Completo não está disponível no momento. Configure o Stripe (STRIPE_PRICE_FULL) ou contacte o suporte.'),
            ]);
        }

        $email = Str::lower(trim((string) $request->email));

        $base = Str::slug($request->empresa_nome);
        $base = $base !== '' ? $base : 'empresa';
        $candidate = $base;
        $i = 0;
        while (Empresa::query()->where('slug', $candidate)->exists()) {
            $i++;
            $candidate = $base.'-'.$i;
        }
        $slug = $candidate;

        try {
            [$empresa, $user, $checkoutUrl] = DB::transaction(function () use ($request, $slug, $email): array {
                $empresa = Empresa::query()->create([
                    'nome' => $request->empresa_nome,
                    'slug' => $slug,
                    'ativo' => true,
                    'email_contato' => $email,
                    'pagamento_inicial_pendente' => true,
                ]);

                $user = User::query()->create([
                    'empresa_id' => $empresa->id,
                    'name' => $request->name,
                    'email' => $email,
                    'password' => Hash::make($request->password),
                ]);

                $rbac = app(EmpresaRbacService::class);
                $rbac->bootstrapEmpresa($empresa);
                $rbac->assignRole($user, 'administrador');

                $session = $this->checkout->createSubscriptionCheckoutSession(
                    $empresa,
                    $user,
                    'completa',
                    $email,
                );

                $url = is_string($session->url ?? null) ? $session->url : '';

                return [$empresa, $user, $url];
            });
        } catch (\Throwable $e) {
            Log::error('Registo: falha ao criar empresa ou sessão Stripe.', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['empresa_nome' => __('Não foi possível iniciar o registo. Tente novamente ou contacte o suporte.')]);
        }

        if ($checkoutUrl === '') {
            return back()
                ->withInput()
                ->withErrors(['empresa_nome' => __('Resposta inválida do serviço de pagamento.')]);
        }

        event(new Registered($user));

        Auth::login($user);

        app(EmpresaProcessosDefaultsService::class)->garantirTemplateBasico($empresa);

        if ($this->nxAuthSpa($request)) {
            return response()->json([
                'redirect' => $checkoutUrl,
            ]);
        }

        return redirect()->away($checkoutUrl);
    }
}
