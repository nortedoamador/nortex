<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSubscription
{
    /**
     * @var list<string>
     */
    private const ALLOWED_WITHOUT_SUBSCRIPTION = [
        'dashboard',
        'planos.index',
        'planos.checkout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->empresa_id) {
            return $next($request);
        }

        $user->loadMissing('empresa');
        $empresa = $user->empresa;
        if ($empresa === null) {
            return $next($request);
        }

        if ((int) $request->session()->get('impersonator_id', 0) > 0) {
            return $next($request);
        }

        if ($empresa->assinaturaPlataformaAtiva()) {
            return $next($request);
        }

        if ($request->routeIs(self::ALLOWED_WITHOUT_SUBSCRIPTION)) {
            return $next($request);
        }

        return redirect()
            ->route('dashboard')
            ->with(
                'status',
                __('A sua organização não tem um plano ativo. Assine na área Planos para continuar.'),
            );
    }
}
