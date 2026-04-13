<?php

use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\TipoProcesso;

if (! function_exists('tenant_admin_route')) {
    /**
     * Gera URL para rotas admin da empresa (tenant) ou admin da plataforma por empresa.
     *
     * @param  array<string, mixed>|Role|TipoProcesso|DocumentoTipo  $parameters
     */
    function tenant_admin_route(string $suffix, array|Role|TipoProcesso|DocumentoTipo $parameters = []): string
    {
        $routeParams = $parameters;
        if ($parameters instanceof Role) {
            $routeParams = ['papel' => $parameters];
        } elseif ($parameters instanceof TipoProcesso) {
            $routeParams = ['tipo_processo' => $parameters];
        } elseif ($parameters instanceof DocumentoTipo) {
            $routeParams = ['documento_tipo' => $parameters];
        }

        if (\App\Support\TenantEmpresaContext::isPlatformEmpresaAdminRoute()) {
            $empresa = \App\Support\TenantEmpresaContext::routeEmpresa();
            if ($empresa instanceof Empresa) {
                return route('platform.empresas.admin.'.$suffix, array_merge(['empresa' => $empresa], $routeParams));
            }
        }

        return route('admin.'.$suffix, $routeParams);
    }
}

if (! function_exists('tenant_admin_route_name')) {
    function tenant_admin_route_name(string $suffix): string
    {
        if (\App\Support\TenantEmpresaContext::isPlatformEmpresaAdminRoute()) {
            return 'platform.empresas.admin.'.$suffix;
        }

        return 'admin.'.$suffix;
    }
}

if (! function_exists('tenant_doc_modelo_route')) {
    /**
     * Rotas `documento-modelos.*` no tenant ou sob `platform.empresas.admin.documento-modelos.*`.
     *
     * @param  array<string, mixed>  $parameters
     */
    function tenant_doc_modelo_route(string $suffix, array $parameters = []): string
    {
        if (\App\Support\TenantEmpresaContext::isPlatformEmpresaAdminRoute()) {
            $empresa = \App\Support\TenantEmpresaContext::routeEmpresa();
            if ($empresa instanceof Empresa) {
                return route('platform.empresas.admin.documento-modelos.'.$suffix, array_merge(['empresa' => $empresa], $parameters));
            }
        }

        return route('documento-modelos.'.$suffix, $parameters);
    }
}
