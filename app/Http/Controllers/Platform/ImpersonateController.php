<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PlatformActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    public function __construct(
        private PlatformActivityLogService $platformLog,
    ) {}

    public function start(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor && $actor->is_platform_admin, 403);

        if ((int) $actor->id === (int) $user->id) {
            return back()->withErrors(['impersonate' => __('Você já está logado neste usuário.')]);
        }

        $request->session()->put('impersonator_id', (int) $actor->id);
        $request->session()->put('impersonated_user_id', (int) $user->id);

        Auth::login($user);
        $request->session()->regenerate();

        $this->platformLog->log(
            'platform_impersonate_start',
            __(':actor iniciou impersonate para :target (:email).', [
                'actor' => $actor->name,
                'target' => $user->name,
                'email' => $user->email,
            ]),
            $user->empresa_id ? (int) $user->empresa_id : null,
            User::class,
            (int) $user->id,
        );

        return redirect()->route('dashboard')->with('status', __('Impersonate iniciado.'));
    }

    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = (int) $request->session()->get('impersonator_id', 0);
        $impersonatedId = (int) $request->session()->get('impersonated_user_id', 0);

        if ($impersonatorId <= 0) {
            return redirect()->route('home');
        }

        $impersonator = User::query()->find($impersonatorId);
        $impersonated = $impersonatedId > 0 ? User::query()->find($impersonatedId) : null;

        Auth::logout();

        $request->session()->forget(['impersonator_id', 'impersonated_user_id']);

        if ($impersonator) {
            Auth::login($impersonator);
        }

        $request->session()->regenerate();

        if ($impersonator && $impersonated) {
            $this->platformLog->log(
                'platform_impersonate_stop',
                __(':actor finalizou impersonate de :target (:email).', [
                    'actor' => $impersonator->name,
                    'target' => $impersonated->name,
                    'email' => $impersonated->email,
                ]),
                $impersonated->empresa_id ? (int) $impersonated->empresa_id : null,
                User::class,
                (int) $impersonated->id,
            );
        }

        return redirect()->route('platform.dashboard')->with('status', __('Impersonate finalizado.'));
    }
}

