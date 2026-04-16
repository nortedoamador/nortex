<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantEmpresa
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Usuário sem empresa vinculada.');
        }

        if ($user->empresa_id) {
            $user->loadMissing('empresa');
            $empresa = $user->empresa;
            if ($empresa === null) {
                abort(403, __('Empresa não encontrada para este utilizador.'));
            }

            if (! $empresa->acessoPlataformaVigente()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login', ['nx_rev' => 1])
                    ->with(
                        'status',
                        __('O acesso da sua organização terminou em :data. Contacte o suporte se precisar de renovar.', [
                            'data' => $empresa->acesso_plataforma_ate?->format('d/m/Y') ?? '—',
                        ]),
                    );
            }

            if ((int) $request->session()->get('impersonator_id', 0) > 0) {
                return $next($request);
            }

            return $next($request);
        }

        if ($user->is_platform_admin) {
            return redirect()->route('platform.empresas.index');
        }

        abort(403, 'Usuário sem empresa vinculada.');
    }
}
