@php
    $empresa = \App\Support\TenantEmpresaContext::routeEmpresa();
@endphp
@if ($empresa instanceof \App\Models\Empresa)
    <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-4 dark:border-slate-700">
        <a href="{{ route('platform.empresas.edit', $empresa) }}" class="text-xs font-semibold text-slate-500 hover:text-violet-600 dark:text-slate-400 dark:hover:text-violet-400">{{ __('Dados da empresa') }}</a>
        <span class="text-slate-300 dark:text-slate-600" aria-hidden="true">|</span>
        <a href="{{ tenant_admin_route('roles.index') }}" class="text-xs font-semibold {{ request()->routeIs('platform.empresas.admin.roles.*') ? 'text-violet-600 dark:text-violet-400' : 'text-slate-500 hover:text-violet-600 dark:text-slate-400 dark:hover:text-violet-400' }}">{{ __('Papéis') }}</a>
        <a href="{{ tenant_admin_route('tipo-processos.index') }}" class="text-xs font-semibold {{ request()->routeIs('platform.empresas.admin.tipo-processos.*') ? 'text-violet-600 dark:text-violet-400' : 'text-slate-500 hover:text-violet-600 dark:text-slate-400 dark:hover:text-violet-400' }}">{{ __('Tipos de processo') }}</a>
        <a href="{{ tenant_admin_route('documento-tipos.index') }}" class="text-xs font-semibold {{ request()->routeIs('platform.empresas.admin.documento-tipos.*') ? 'text-violet-600 dark:text-violet-400' : 'text-slate-500 hover:text-violet-600 dark:text-slate-400 dark:hover:text-violet-400' }}">{{ __('Tipos de documento') }}</a>
        <a href="{{ tenant_admin_route('documento-modelos.laboratorio') }}" class="text-xs font-semibold {{ request()->routeIs('platform.empresas.admin.documento-modelos.*') ? 'text-violet-600 dark:text-violet-400' : 'text-slate-500 hover:text-violet-600 dark:text-slate-400 dark:hover:text-violet-400' }}">{{ __('Laboratório PDF') }}</a>
        <a href="{{ tenant_admin_route('auditoria.index') }}" class="text-xs font-semibold {{ request()->routeIs('platform.empresas.admin.auditoria.*') ? 'text-violet-600 dark:text-violet-400' : 'text-slate-500 hover:text-violet-600 dark:text-slate-400 dark:hover:text-violet-400' }}">{{ __('Auditoria') }}</a>
        <a href="{{ tenant_admin_route('relatorios.index') }}" class="text-xs font-semibold {{ request()->routeIs('platform.empresas.admin.relatorios.*') ? 'text-violet-600 dark:text-violet-400' : 'text-slate-500 hover:text-violet-600 dark:text-slate-400 dark:hover:text-violet-400' }}">{{ __('Relatórios') }}</a>
    </div>
@endif
