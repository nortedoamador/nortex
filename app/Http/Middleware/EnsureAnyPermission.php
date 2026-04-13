<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAnyPermission
{
    /**
     * @param  string  $permissions  Slugs separados por | (ex.: cadastros.manage|clientes.manage)
     */
    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        $user = $request->user();
        foreach (explode('|', $permissions) as $slug) {
            $slug = trim($slug);
            if ($slug !== '' && $user && $user->hasPermission($slug)) {
                return $next($request);
            }
        }

        abort(403, 'Permissão negada.');
    }
}
