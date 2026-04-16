<?php

use App\Models\DocumentoTipo;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\TipoProcesso;
use App\Support\TenantEmpresaContext;
use Illuminate\Support\Str;

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

        if (TenantEmpresaContext::isPlatformEmpresaAdminRoute()) {
            $empresa = TenantEmpresaContext::routeEmpresa();
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
        if (TenantEmpresaContext::isPlatformEmpresaAdminRoute()) {
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
        if (TenantEmpresaContext::isPlatformEmpresaAdminRoute()) {
            $empresa = TenantEmpresaContext::routeEmpresa();
            if ($empresa instanceof Empresa) {
                return route('platform.empresas.admin.documento-modelos.'.$suffix, array_merge(['empresa' => $empresa], $parameters));
            }
        }

        return route('documento-modelos.'.$suffix, $parameters);
    }
}

if (! function_exists('activity_log_action_label')) {
    /**
     * Rótulo curto em português para o campo `action` de activity_logs.
     */
    function activity_log_action_label(string $action): string
    {
        $key = 'activity_log.actions.'.$action;
        $trans = __($key);

        if ($trans !== $key) {
            return $trans;
        }

        return Str::title(str_replace('_', ' ', $action));
    }
}

if (! function_exists('upload_max_kb')) {
    function upload_max_kb(): int
    {
        return max(256, (int) config('uploads.max_kb', 3072));
    }
}

if (! function_exists('upload_max_file_help')) {
    /**
     * Rótulo legível para o limite de upload (ex.: «3 MB»).
     */
    function upload_max_file_help(): string
    {
        $kb = upload_max_kb();
        if ($kb % 1024 === 0) {
            return (string) ((int) ($kb / 1024)).' MB';
        }

        return rtrim(rtrim(number_format($kb / 1024, 2, ',', ''), '0'), ',').' MB';
    }
}
