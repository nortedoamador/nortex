<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use App\Services\PlatformActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function __construct(
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

    public function edit(User $user): View
    {
        $empresas = Empresa::query()->orderBy('nome')->get(['id', 'nome']);

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

