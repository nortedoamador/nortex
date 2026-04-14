@php
    $empresa = \App\Support\TenantEmpresaContext::routeEmpresa();
@endphp
@if ($empresa instanceof \App\Models\Empresa)
    <div class="flex flex-col gap-3 border-b border-slate-200 pb-4 dark:border-slate-700">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Admin desta empresa') }}</p>
            <a href="{{ route('platform.empresas.show', $empresa) }}" class="text-xs font-medium text-violet-600 hover:text-violet-500 dark:text-violet-400">{{ __('← Painel da empresa') }}</a>
        </div>
        <nav class="flex flex-wrap gap-2" aria-label="{{ __('Secções do admin da empresa') }}">
            <a href="{{ route('platform.empresas.show', $empresa) }}" class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold transition {{ request()->routeIs('platform.empresas.show') ? 'bg-violet-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700' }}">{{ __('Painel') }}</a>
            <a href="{{ route('platform.empresas.edit', $empresa) }}" class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold transition {{ request()->routeIs('platform.empresas.edit') ? 'bg-violet-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700' }}">{{ __('Dados') }}</a>
            <a href="{{ tenant_admin_route('roles.index') }}" class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold transition {{ request()->routeIs('platform.empresas.admin.roles.*') ? 'bg-violet-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700' }}">{{ __('Papéis') }}</a>
            <a href="{{ tenant_admin_route('tipo-processos.index') }}" class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold transition {{ request()->routeIs('platform.empresas.admin.tipo-processos.*') ? 'bg-violet-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700' }}">{{ __('Tipos de processo') }}</a>
            <a href="{{ tenant_admin_route('documento-tipos.index') }}" class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold transition {{ request()->routeIs('platform.empresas.admin.documento-tipos.*') ? 'bg-violet-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700' }}">{{ __('Tipos de documento') }}</a>
            <a href="{{ tenant_admin_route('documento-modelos.laboratorio') }}" class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold transition {{ request()->routeIs('platform.empresas.admin.documento-modelos.*') ? 'bg-violet-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700' }}">{{ __('Laboratório PDF') }}</a>
            <a href="{{ tenant_admin_route('auditoria.index') }}" class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold transition {{ request()->routeIs('platform.empresas.admin.auditoria.*') ? 'bg-violet-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700' }}">{{ __('Auditoria') }}</a>
            <a href="{{ tenant_admin_route('relatorios.index') }}" class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold transition {{ request()->routeIs('platform.empresas.admin.relatorios.*') ? 'bg-violet-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700' }}">{{ __('Relatórios') }}</a>
        </nav>
    </div>
@endif
