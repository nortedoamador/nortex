<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlatformEmpresaAdminUserRequest;
use App\Models\Empresa;
use App\Models\User;
use App\Services\EmpresaRbacService;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmpresaAdminUserController extends Controller
{
    public function __construct(
        private EmpresaRbacService $empresaRbac,
        private PlanLimitService $planLimits,
    ) {}

    public function store(StorePlatformEmpresaAdminUserRequest $request, Empresa $empresa): RedirectResponse
    {
        if (! $this->planLimits->empresaPodeCriarMaisUsuarios($empresa)) {
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
            'empresa_id' => $empresa->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $senhaInicial,
        ]);

        $this->empresaRbac->assignRole($user, 'administrador');

        $status = __('Utilizador administrador da empresa criado.');

        if ($enviarConvite) {
            $mailStatus = Password::sendResetLink(['email' => $user->email]);
            if ($mailStatus === Password::RESET_LINK_SENT) {
                $status = __('Utilizador criado. Foi enviado um e-mail com o link para definir a senha.');
            } else {
                Log::warning('Falha ao enviar convite por e-mail (utilizador admin criado pela plataforma).', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'password_broker_status' => $mailStatus,
                ]);
                $status = __('Utilizador criado, mas o e-mail de convite não foi enviado. Configure o correio (SMTP) ou redefina a senha pela área Plataforma → Usuários.');
            }
        }

        return redirect()
            ->route('platform.empresas.edit', $empresa)
            ->with('status', $status);
    }
}
