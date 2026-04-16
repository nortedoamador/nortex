<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloqueia o módulo financeiro quando o plano de billing não o inclui (ex.: Stripe Essencial).
 */
final class EnsureTenantFinanceiroBillingAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $empresa = $user?->empresa;

        if ($empresa !== null && ! $empresa->billingIncludesFinanceiro()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('O módulo financeiro não está disponível no seu plano atual.'),
                ], 403);
            }

            return redirect()
                ->route('dashboard')
                ->with('error', __('O módulo financeiro não está disponível no seu plano atual. Faça upgrade para o plano Completo para aceder.'));
        }

        return $next($request);
    }
}
