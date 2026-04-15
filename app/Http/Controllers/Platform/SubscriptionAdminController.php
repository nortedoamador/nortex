<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StorePlatformSubscriptionEmpresaRequest;
use App\Http\Requests\Platform\StoreSubscriptionManualRequest;
use App\Models\Empresa;
use App\Models\User;
use App\Services\EmpresaRbacService;
use App\Services\StripeBillingSyncService;
use App\Support\BrazilStates;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriptionAdminController extends Controller
{
    public function __construct(
        private StripeBillingSyncService $stripeBilling,
        private EmpresaRbacService $empresaRbac,
    ) {}

    public function adicionarCreate(): View
    {
        return view('platform.assinaturas.adicionar', [
            'ufs' => BrazilStates::labels(),
        ]);
    }

    public function adicionarStore(StorePlatformSubscriptionEmpresaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $norm = static function (?string $s): ?string {
            if (! is_string($s)) {
                return null;
            }
            $t = trim($s);

            return $t === '' ? null : $t;
        };

        $slugInput = $norm($data['slug'] ?? null);
        $slug = $slugInput ?? $this->uniqueSlugFromNome($data['nome']);

        $status = $norm($data['stripe_subscription_status'] ?? null);
        $subId = $norm($data['stripe_subscription_id'] ?? null);

        try {
            $empresa = DB::transaction(function () use ($request, $data, $norm, $slug, $status, $subId): Empresa {
                $empresa = Empresa::query()->create([
                    'nome' => $data['nome'],
                    'slug' => $slug,
                    'email_contato' => Str::lower(trim($data['email_contato'])),
                    'telefone' => $norm($data['telefone'] ?? null),
                    'cnpj' => $norm($data['cnpj'] ?? null),
                    'uf' => $norm($data['uf'] ?? null),
                    'ativo' => $request->boolean('ativo', true),
                    'pagamento_inicial_pendente' => false,
                    'acesso_plataforma_ate' => $data['acesso_plataforma_ate'] ?? null,
                    'stripe_customer_id' => $norm($data['stripe_customer_id'] ?? null),
                    'stripe_subscription_id' => $subId,
                    'stripe_subscription_status' => $status,
                    'stripe_current_price_id' => $norm($data['stripe_current_price_id'] ?? null),
                    'stripe_subscription_cancel_at_period_end' => $subId
                        ? $request->boolean('stripe_subscription_cancel_at_period_end')
                        : false,
                ]);

                $this->empresaRbac->bootstrapEmpresa($empresa);

                $user = User::query()->create([
                    'empresa_id' => $empresa->id,
                    'name' => $data['admin_name'],
                    'email' => Str::lower(trim($data['admin_email'])),
                    'password' => Str::password(48),
                    'is_disabled' => false,
                ]);

                $this->empresaRbac->assignRole($user, 'administrador');

                if ($request->has('enviar_convite')) {
                    $mailStatus = Password::sendResetLink(['email' => $user->email]);
                    if ($mailStatus !== Password::RESET_LINK_SENT) {
                        Log::warning('Assinaturas adicionar: falha ao enviar convite ao admin.', [
                            'empresa_id' => $empresa->id,
                            'user_id' => $user->id,
                            'status' => $mailStatus,
                        ]);
                    }
                }

                return $empresa;
            });
        } catch (\Throwable $e) {
            Log::error('Assinaturas: falha ao criar empresa.', ['message' => $e->getMessage()]);

            return redirect()
                ->route('platform.assinaturas.adicionar')
                ->withInput()
                ->withErrors(['geral' => __('Não foi possível criar a empresa. :m', ['m' => $e->getMessage()])]);
        }

        return redirect()
            ->route('platform.assinaturas.index', ['empresa_id' => $empresa->id])
            ->with('status', __('Empresa :nome criada. Pode ajustar a assinatura Stripe na lista acima.', ['nome' => $empresa->nome]));
    }

    public function manualCreate(Request $request): RedirectResponse
    {
        $params = $request->query();
        if (array_key_exists('q', $params) && ! array_key_exists('pick_q', $params)) {
            $params['pick_q'] = $params['q'];
            unset($params['q']);
        }

        return redirect()->route('platform.assinaturas.index', $params);
    }

    public function manualStore(StoreSubscriptionManualRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $empresa = Empresa::query()->findOrFail((int) $data['empresa_id']);

        $norm = static function (?string $s): ?string {
            if (! is_string($s)) {
                return null;
            }
            $t = trim($s);

            return $t === '' ? null : $t;
        };

        $status = $norm($data['stripe_subscription_status'] ?? null);
        $subId = $norm($data['stripe_subscription_id'] ?? null);

        $attrs = [
            'stripe_customer_id' => $norm($data['stripe_customer_id'] ?? null),
            'stripe_subscription_id' => $subId,
            'stripe_subscription_status' => $status,
            'stripe_current_price_id' => $norm($data['stripe_current_price_id'] ?? null),
            'stripe_subscription_cancel_at_period_end' => $subId
                ? $request->boolean('stripe_subscription_cancel_at_period_end')
                : false,
            'acesso_plataforma_ate' => $data['acesso_plataforma_ate'] ?? null,
        ];

        if ($subId && in_array($status, ['active', 'trialing'], true)) {
            $attrs['pagamento_inicial_pendente'] = false;
        }

        $empresa->forceFill($attrs)->save();

        return redirect()
            ->route('platform.assinaturas.index', array_filter([
                'q' => $request->input('return_q'),
                'filtro' => $request->input('return_filtro'),
                'pick_q' => $request->input('return_pick_q'),
                'empresa_id' => $empresa->id,
            ], fn ($v) => $v !== null && $v !== ''))
            ->with('status', __('Dados de assinatura guardados manualmente para :nome.', ['nome' => $empresa->nome]));
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $filtro = $request->query('filtro', 'todas');

        $query = Empresa::query()
            ->orderByDesc('updated_at');

        if ($filtro === 'com_stripe') {
            $query->where(function ($w) {
                $w->whereNotNull('stripe_customer_id')
                    ->orWhereNotNull('stripe_subscription_id');
            });
        } elseif ($filtro === 'sub_ativa') {
            $query->whereIn('stripe_subscription_status', ['active', 'trialing']);
        } elseif ($filtro === 'todas') {
            // sem filtro extra
        }

        if ($q !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($w) use ($termo) {
                $w->where('nome', 'like', $termo)
                    ->orWhere('slug', 'like', $termo)
                    ->orWhere('email_contato', 'like', $termo);
            });
        }

        $empresas = $query->paginate(25)->withQueryString();

        $manualCtx = $this->manualStripeFormContext($request);

        return view('platform.assinaturas.index', [
            'empresas' => $empresas,
            'q' => $q,
            'filtro' => $filtro,
            'empresaEdicao' => $manualCtx['empresaEdicao'],
            'empresaListaManual' => $manualCtx['empresaListaManual'],
            'pickQ' => $manualCtx['pickQ'],
        ]);
    }

    /**
     * @return array{empresaEdicao: ?Empresa, empresaListaManual: EloquentCollection<int, Empresa>, pickQ: string}
     */
    private function manualStripeFormContext(Request $request): array
    {
        $pickQ = trim((string) $request->query('pick_q', ''));
        $empresaId = $request->query('empresa_id');
        $empresaEdicao = null;
        if ($empresaId !== null && $empresaId !== '' && ctype_digit((string) $empresaId)) {
            $empresaEdicao = Empresa::query()->find((int) $empresaId);
        }

        $listaQuery = Empresa::query()->orderBy('nome')->limit(500);
        if ($pickQ !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $pickQ).'%';
            $listaQuery->where(function ($w) use ($termo) {
                $w->where('nome', 'like', $termo)
                    ->orWhere('slug', 'like', $termo)
                    ->orWhere('email_contato', 'like', $termo);
            });
        }
        $empresaListaManual = $listaQuery->get(['id', 'nome', 'slug', 'email_contato']);

        if ($empresaEdicao !== null && ! $empresaListaManual->pluck('id')->contains($empresaEdicao->id)) {
            $empresaListaManual = $empresaListaManual->prepend($empresaEdicao);
        }

        return [
            'empresaEdicao' => $empresaEdicao,
            'empresaListaManual' => $empresaListaManual,
            'pickQ' => $pickQ,
        ];
    }

    public function sync(Empresa $empresa): RedirectResponse
    {
        try {
            $ok = $this->stripeBilling->syncFromStripeForEmpresa($empresa);
        } catch (\Throwable $e) {
            Log::warning('Platform assinaturas: sync falhou.', [
                'empresa_id' => $empresa->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors(['stripe' => __('Erro ao sincronizar com o Stripe: :m', ['m' => $e->getMessage()])]);
        }

        return redirect()->back()->with('status', $ok
                ? __('Dados da assinatura atualizados a partir do Stripe.')
                : __('Não foi possível sincronizar (sem customer/subscrição no Stripe ou API indisponível).'));
    }

    public function cancelarNoFimDoPeriodo(Empresa $empresa): RedirectResponse
    {
        try {
            $this->stripeBilling->setSubscriptionCancelAtPeriodEnd($empresa, true);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['stripe' => $e->getMessage()]);
        }

        return redirect()->back()->with('status', __('Renovação automática desativada: a subscrição termina no fim do período atual.'));
    }

    public function manterRenovacao(Empresa $empresa): RedirectResponse
    {
        try {
            $this->stripeBilling->setSubscriptionCancelAtPeriodEnd($empresa, false);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['stripe' => $e->getMessage()]);
        }

        return redirect()->back()->with('status', __('Renovação automática reativada.'));
    }

    public function reenviarSenhaAdmin(Empresa $empresa): RedirectResponse
    {
        $user = $this->resolveAdminContactUser($empresa);
        if (! $user) {
            return redirect()->back()->withErrors(['stripe' => __('Não foi encontrado um administrador ou utilizador com o e-mail de contacto desta empresa.')]);
        }

        $mailStatus = Password::sendResetLink(['email' => $user->email]);
        if ($mailStatus !== Password::RESET_LINK_SENT) {
            Log::warning('Platform assinaturas: falha ao enviar reset de senha.', [
                'empresa_id' => $empresa->id,
                'user_id' => $user->id,
                'status' => $mailStatus,
            ]);

            return redirect()->back()->withErrors(['stripe' => __('Não foi possível enviar o e-mail. Verifique a configuração SMTP.')]);
        }

        return redirect()->back()->with('status', __('Link para definir senha enviado para :e.', ['e' => $user->email]));
    }

    private function resolveAdminContactUser(Empresa $empresa): ?User
    {
        $admin = User::query()
            ->where('empresa_id', $empresa->id)
            ->where('is_platform_admin', false)
            ->whereHas('roles', function ($q) use ($empresa) {
                $q->where('roles.empresa_id', $empresa->id)
                    ->where('roles.slug', 'administrador');
            })
            ->orderBy('id')
            ->first();

        if ($admin) {
            return $admin;
        }

        $email = $empresa->email_contato;
        if (! is_string($email) || $email === '') {
            return null;
        }

        $norm = Str::lower(trim($email));

        return User::query()
            ->where('empresa_id', $empresa->id)
            ->whereRaw('LOWER(email) = ?', [$norm])
            ->orderBy('id')
            ->first();
    }

    private function uniqueSlugFromNome(string $nome): string
    {
        $base = Str::slug($nome);
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
