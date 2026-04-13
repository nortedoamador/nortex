<?php

namespace App\Support;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;

final class TenantEmpresaContext
{
    public static function isPlatformEmpresaAdminRoute(?Request $request = null): bool
    {
        $request ??= request();

        $name = $request->route()?->getName() ?? '';

        return str_starts_with($name, 'platform.empresas.admin.');
    }

    /**
     * Empresa do segmento `{empresa}` (modelo ou id), quando existir na rota atual.
     */
    public static function routeEmpresa(?Request $request = null): ?Empresa
    {
        $request ??= request();

        if ($request->attributes->has('tenant_route_empresa')) {
            /** @var Empresa|null */
            return $request->attributes->get('tenant_route_empresa');
        }

        $e = $request->route('empresa');
        $resolved = null;
        if ($e instanceof Empresa) {
            $resolved = $e;
        } elseif ($e !== null && $e !== '' && is_numeric($e)) {
            $resolved = Empresa::query()->find((int) $e);
        }

        $request->attributes->set('tenant_route_empresa', $resolved);

        return $resolved;
    }

    public static function empresaId(Request $request): int
    {
        if (self::isPlatformEmpresaAdminRoute($request) && $request->user()?->is_platform_admin) {
            $e = $request->route('empresa');
            if ($e instanceof Empresa) {
                $request->attributes->set('tenant_route_empresa', $e);

                return (int) $e->id;
            }
            if ($e !== null && $e !== '' && is_numeric($e)) {
                $empresa = Empresa::query()->findOrFail((int) $e);
                $request->attributes->set('tenant_route_empresa', $empresa);

                return (int) $empresa->id;
            }

            abort(404);
        }

        $resolved = self::routeEmpresa($request);
        if ($resolved !== null) {
            return (int) $resolved->id;
        }

        $id = (int) ($request->user()?->empresa_id ?? 0);
        if ($id < 1) {
            abort(403);
        }

        return $id;
    }

    public static function canAccessLaboratorioPdf(User $user, Request $request): bool
    {
        if (self::isPlatformEmpresaAdminRoute($request)) {
            return $user->is_platform_admin;
        }

        return $user->hasPermission('cadastros.manage') || $user->hasPermission('clientes.manage');
    }

    public static function canAccessDocumentoModeloVerificacao(User $user, Request $request): bool
    {
        if (self::isPlatformEmpresaAdminRoute($request)) {
            return $user->is_platform_admin;
        }

        return $user->hasPermission('cadastros.manage') || $user->hasPermission('clientes.manage');
    }

    public static function canEditDocumentoModeloConteudo(User $user, Request $request): bool
    {
        if (self::isPlatformEmpresaAdminRoute($request)) {
            return $user->is_platform_admin;
        }

        return $user->hasPermission('clientes.manage');
    }
}
