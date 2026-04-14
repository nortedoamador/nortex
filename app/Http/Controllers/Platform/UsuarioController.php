<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlatformUsuarioEmpresaRequest;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use App\Services\EquipeLogService;
use App\Services\PlanLimitService;
use App\Services\PlatformActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function __construct(
        private EquipeLogService $equipeLog,
        private PlanLimitService $planLimits,
        private PlatformActivityLogService $platformLog,
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $empresaId = max(0, (int) $request->query('empresa_id', 0));

        $query = User::query()->with('empresa')->orderBy('name');

        if ($empresaId > 0) {
            $query->where('empresa_id', $empresaId);
        }

        if ($q !== '') {
            $termo = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($qq) use ($termo) {
                $qq->where('name', 'like', $termo)->orWhere('email', 'like', $termo);
            });
        }

        $usuarios = $query->paginate(30)->withQueryString();
        $empresas = Empresa::query()->orderBy('nome')->get(['id', 'nome']);

        return view('platform.usuarios.index', compact('usuarios', 'q', 'empresaId', 'empresas'));
    }

    public function create(Request $request): View
    {
        $selectedEmpresaId = max(0, (int) $request->query('empresa_id', 0));
        $empresas = Empresa::query()->orderBy('nome')->get(['id', 'nome']);
        $rolesByEmpresa = Role::query()
            ->whereIn('empresa_id', $empresas->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'empresa_id', 'name', 'slug'])
            ->groupBy('empresa_id');

        return view('platform.usuarios.create', compact('empresas', 'rolesByEmpresa', 'selectedEmpresaId'));
    }

    public function store(StorePlatformUsuarioEmpresaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $empresa = Empresa::query()->findOrFail((int) $data['empresa_id']);

        if (! $this->planLimits->empresaPodeCriarMaisUsuarios($empresa)) {
            throw ValidationException::withMessages([
                'email' => __('Limite de usuários do plano atingido.'),
            ]);
        }

        $enviarConvite = $request->boolean('enviar_convite');
        $senhaInicial = $enviarConvite
            ? Str::password(48)
            : $data['password'];

        $user = User::query()->create([
            'empresa_id' => $empresa->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $senhaInicial,
        ]);

        $user->roles()->sync($data['roles']);

        $papeis = $this->equipeLog->nomesPapeis((int) $empresa->id, $data['roles']);

        $this->equipeLog->registrar(
            (int) $empresa->id,
            $request->user(),
            $user,
            'user_created',
            __(':actor criou o usuário :nome (:email).', [
                'actor' => $request->user()->name,
                'nome' => $user->name,
                'email' => $user->email,
            ]),
            array_filter([
                'papeis' => $papeis,
                'origem' => 'platform',
                'convite_por_email' => $enviarConvite ?: null,
            ]),
        );

        $this->platformLog->log(
            'platform_company_user_created',
            __('Usuário criado para a empresa :empresa: :email', [
                'empresa' => $empresa->nome,
                'email' => $user->email,
            ]),
            (int) $empresa->id,
            User::class,
            (int) $user->id,
            ['papeis' => $papeis],
        );

        $status = __('Usuário criado e papéis atribuídos.');

        if ($enviarConvite) {
            $mailStatus = Password::sendResetLink(['email' => $user->email]);
            if ($mailStatus === Password::RESET_LINK_SENT) {
                $status = __('Usuário criado. Foi enviado um e-mail com o link para definir a senha.');
            } else {
                Log::warning('Falha ao enviar convite por e-mail (utilizador criado pela plataforma).', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'password_broker_status' => $mailStatus,
                ]);
                $status = __('Usuário criado, mas o e-mail de convite não foi enviado. Configure o correio (SMTP) ou use “Enviar reset de senha” na edição do utilizador.');
            }
        }

        return redirect()
            ->route('platform.usuarios.edit', $user)
            ->with('status', $status);
    }

    public function edit(User $user): View
    {
        $empresas = Empresa::query()->orderBy('nome')->get(['id', 'nome']);
        $user->load([
            'empresa:id,nome',
            'roles' => fn ($q) => $q->orderBy('name'),
        ]);

        return view('platform.usuarios.edit', compact('user', 'empresas'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'empresa_id' => ['nullable', 'integer', Rule::exists('empresas', 'id')],
            'is_platform_admin' => ['nullable', 'boolean'],
            'is_disabled' => ['nullable', 'boolean'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'empresa_id' => $data['empresa_id'] ?? null,
            'is_platform_admin' => $request->boolean('is_platform_admin'),
            'is_disabled' => $request->boolean('is_disabled'),
        ]);

        $this->platformLog->log(
            'platform_user_updated',
            __('Usuário global atualizado: :email', ['email' => $user->email]),
            $user->empresa_id ? (int) $user->empresa_id : null,
            User::class,
            (int) $user->id,
        );

        return redirect()->route('platform.usuarios.index')->with('status', __('Usuário atualizado.'));
    }

    public function sendPasswordReset(Request $request, User $user): RedirectResponse
    {
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->platformLog->log(
                'platform_user_password_reset_sent',
                __('Reset de senha enviado para :email', ['email' => $user->email]),
                $user->empresa_id ? (int) $user->empresa_id : null,
                User::class,
                (int) $user->id,
            );

            return back()->with('status', __('Foi enviado um e-mail de redefinição de senha.'));
        }

        return back()->withErrors(['email' => __($status)]);
    }
}

