<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use App\Services\PlatformActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Regista na auditoria da plataforma pedidos mutantes bem-sucedidos (POST/PUT/PATCH/DELETE).
 * Rotas com registo manual explícito são ignoradas para evitar duplicados.
 */
class LogPlatformHttpMutation
{
    private const SKIP_ROUTE_NAMES = [
        'platform.impersonate.start',
        'platform.usuarios.store',
        'platform.usuarios.update',
        'platform.usuarios.password-reset',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        $status = $response->getStatusCode();
        if ($status >= 400) {
            return;
        }

        $route = $request->route();
        if (! $route) {
            return;
        }

        $name = $route->getName();
        if (! is_string($name) || ! str_starts_with($name, 'platform.')) {
            return;
        }

        if (in_array($name, self::SKIP_ROUTE_NAMES, true)) {
            return;
        }

        $user = $request->user();
        if (! $user) {
            return;
        }

        $short = str_starts_with($name, 'platform.') ? substr($name, strlen('platform.')) : $name;
        $action = 'platform_http_'.str_replace(['.', '-'], '_', $short);

        $summary = __('Pedido :method — :name', [
            'method' => $request->method(),
            'name' => $name,
        ]);

        $empresaId = $this->resolveEmpresaId($route->parameters());

        app(PlatformActivityLogService::class)->log(
            $action,
            $summary,
            $empresaId,
            null,
            null,
            [
                'path' => '/'.$request->path(),
                'route' => $name,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function resolveEmpresaId(array $parameters): ?int
    {
        foreach ($parameters as $param) {
            if ($param instanceof Empresa) {
                return (int) $param->id;
            }
            if (is_object($param) && isset($param->empresa_id) && $param->empresa_id) {
                return (int) $param->empresa_id;
            }
        }

        return null;
    }
}
