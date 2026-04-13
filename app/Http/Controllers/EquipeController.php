<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioEmpresaRequest;
use App\Http\Requests\UpdateUsuarioEquipeRequest;
use App\Models\EquipeLog;
use App\Models\Role;
use App\Models\User;
use App\Services\PlanLimitService;
use App\Services\EquipeAdminService;
use App\Services\EquipeLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EquipeController extends Controller
{
    public function __construct(
        private EquipeAdminService $equipeAdmin,
        private EquipeLogService $equipeLog,
        private PlanLimitService $planLimits,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $usuarios = User::query()
            ->where('empresa_id', $request->user()->empresa_id)
            ->with('roles')
            ->orderBy('name')
            ->get();

        $acaoFiltro = $this->validateAcaoFiltroRegistos($request);

        $logs = EquipeLog::query()
            ->where('empresa_id', $request->user()->empresa_id)
            ->with(['actor', 'subjectUser'])
            ->when($acaoFiltro, fn ($q) => $q->where('action', $acaoFiltro))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('equipe.index', compact('usuarios', 'logs', 'acaoFiltro'));
    }

    public function exportLogs(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', User::class);

        $empresaId = (int) $request->user()->empresa_id;
        $acaoFiltro = $this->validateAcaoFiltroRegistos($request);

        $query = EquipeLog::query()
            ->where('empresa_id', $empresaId)
            ->with(['actor:id,name'])
            ->when($acaoFiltro, fn ($q) => $q->where('action', $acaoFiltro))
            ->orderByDesc('id')
            ->limit(5000);

        $filename = 'equipe-registos-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, '%c%c%c', 0xEF, 0xBB, 0xBF);
            fputcsv($handle, ['id', 'data_hora', 'acao', 'autor', 'resumo', 'meta_json']);

            foreach ($query->get() as $log) {
                $metaJson = '';
                if (is_array($log->meta) && $log->meta !== []) {
                    $metaJson = json_encode($log->meta, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                }

                fputcsv($handle, [
                    $log->id,
                    $log->created_at?->format('Y-m-d H:i:s'),
                    $log->action,
                    $log->actor?->name ?? '',
                    $log->summary,
                    $metaJson,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validateAcaoFiltroRegistos(Request $request): ?string
    {
        $v = $request->validate([
            'acao' => ['nullable', 'string', 'in:user_created,user_updated,user_deleted'],
        ]);

        return $v['acao'] ?? null;
    }

    public function create(Request $request): View
    {
        $this->authorize('create', User::class);

        $roles = Role::query()
            ->where('empresa_id', $request->user()->empresa_id)
            ->orderBy('name')
            ->get();

        return view('equipe.create', compact('roles'));
    }

    public function store(StoreUsuarioEmpresaRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $empresa = $request->user()->empresa;
        if ($empresa && ! $this->planLimits->empresaPodeCriarMaisUsuarios($empresa)) {
            throw ValidationException::withMessages([
                'email' => __('Limite de usuários do plano atingido.'),
            ]);
        }

        $data = $request->validated();
        $enviarConvite = $request->boolean('enviar_convite');

        $senhaInicial = $enviarConvite
            ? Str::password(48)
            : $data['password'];

        $user = User::query()->create([
            'empresa_id' => $request->user()->empresa_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $senhaInicial,
        ]);

        $user->roles()->sync($data['roles']);

        $empresaId = (int) $request->user()->empresa_id;

        $meta = [
            'papeis' => $this->equipeLog->nomesPapeis($empresaId, $data['roles']),
        ];
        if ($enviarConvite) {
            $meta['convite_por_email'] = true;
        }

        $this->equipeLog->registrar(
            $empresaId,
            $request->user(),
            $user,
            'user_created',
            __(':actor criou o usuário :nome (:email).', [
                'actor' => $request->user()->name,
                'nome' => $user->name,
                'email' => $user->email,
            ]),
            $meta,
        );

        $status = __('Usuário criado e papéis atribuídos.');

        if ($enviarConvite) {
            $mailStatus = Password::sendResetLink(['email' => $user->email]);
            if ($mailStatus === Password::RESET_LINK_SENT) {
                $status = __('Usuário criado. Foi enviado um e-mail com o link para definir a senha.');
            } else {
                Log::warning('Falha ao enviar convite por e-mail após criar usuário.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'password_broker_status' => $mailStatus,
                ]);
                $status = __('Usuário criado, mas o e-mail de convite não foi enviado. Configure o correio (SMTP) ou use “Enviar link por e-mail” na edição do usuário.');
            }
        }

        return redirect()
            ->route('equipe.index')
            ->with('status', $status);
    }

    public function sendPasswordResetLink(Request $request, User $usuario): RedirectResponse
    {
        $this->authorize('update', $usuario);

        $status = Password::sendResetLink(['email' => $usuario->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return redirect()
                ->route('equipe.edit', $usuario)
                ->with('status', __('Foi enviado um e-mail para :email com o link para redefinir a senha.', ['email' => $usuario->email]));
        }

        if ($status === Password::RESET_THROTTLED) {
            return redirect()
                ->route('equipe.edit', $usuario)
                ->withErrors(['email' => __($status)]);
        }

        return redirect()
            ->route('equipe.edit', $usuario)
            ->withErrors(['email' => __($status)]);
    }

    public function edit(Request $request, User $usuario): View
    {
        $this->authorize('update', $usuario);

        $roles = Role::query()
            ->where('empresa_id', $request->user()->empresa_id)
            ->orderBy('name')
            ->get();

        $usuario->load('roles');

        return view('equipe.edit', [
            'membro' => $usuario,
            'roles' => $roles,
        ]);
    }

    public function update(UpdateUsuarioEquipeRequest $request, User $usuario): RedirectResponse
    {
        $this->authorize('update', $usuario);

        $data = $request->validated();
        $novos = $data['roles'];
        $empresaId = (int) $request->user()->empresa_id;

        if ($this->equipeAdmin->removerAdministradorDeixaEmpresaSemAdmin($usuario, $empresaId, $novos)) {
            throw ValidationException::withMessages([
                'roles' => __('A empresa precisa de ao menos um administrador. Atribua o papel a outro usuário antes de remover.'),
            ]);
        }

        $usuario->load('roles');
        $meta = $this->equipeLog->metaAlteracoesUsuario($usuario, $data, $novos, $empresaId);

        $attrs = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];
        if (! empty($data['password'])) {
            $attrs['password'] = $data['password'];
        }
        $usuario->update($attrs);

        $usuario->roles()->sync($novos);

        $this->equipeLog->registrar(
            $empresaId,
            $request->user(),
            $usuario->fresh(),
            'user_updated',
            __(':actor atualizou o usuário :nome.', [
                'actor' => $request->user()->name,
                'nome' => $usuario->name,
            ]),
            $meta !== [] ? $meta : null,
        );

        return redirect()
            ->route('equipe.index')
            ->with('status', __('Dados e papéis atualizados.'));
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        $this->authorize('delete', $usuario);

        $empresaId = (int) $request->user()->empresa_id;

        if ($this->equipeAdmin->exclusaoRemoveUltimoAdministrador($usuario, $empresaId)) {
            return redirect()
                ->route('equipe.index')
                ->withErrors([
                    'delete' => __('Não é possível remover o último administrador da empresa.'),
                ]);
        }

        $usuario->load('roles');

        $snapshot = [
            'nome' => $usuario->name,
            'email' => $usuario->email,
            'papeis' => $this->equipeLog->nomesPapeis($empresaId, $usuario->roles->pluck('id')->all()),
        ];

        $this->equipeLog->registrar(
            $empresaId,
            $request->user(),
            $usuario,
            'user_deleted',
            __(':actor removeu o usuário :nome (:email).', [
                'actor' => $request->user()->name,
                'nome' => $usuario->name,
                'email' => $usuario->email,
            ]),
            ['removido' => $snapshot],
        );

        $usuario->roles()->detach();
        $usuario->delete();

        return redirect()
            ->route('equipe.index')
            ->with('status', __('Usuário removido da equipe.'));
    }
}
