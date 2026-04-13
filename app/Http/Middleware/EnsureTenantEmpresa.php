<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
            return $next($request);
        }

        if ($user->is_platform_admin) {
            return redirect()->route('platform.empresas.index');
        }

        abort(403, 'Usuário sem empresa vinculada.');
    }
}

