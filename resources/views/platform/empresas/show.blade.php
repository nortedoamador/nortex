<x-platform-layout :title="$empresa->nome">
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <nav class="mb-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                    <a href="{{ route('platform.empresas.index') }}" class="hover:text-violet-600 dark:hover:text-violet-400">{{ __('Empresas') }}</a>
                    <span class="mx-1.5 text-slate-300 dark:text-slate-600">/</span>
                    <span class="text-slate-700 dark:text-slate-200">{{ __('Painel') }}</span>
                </nav>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ $empresa->nome }}</h2>
                <p class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <span class="font-mono text-xs text-slate-500">{{ $empresa->slug }}</span>
                    @if ($empresa->ativo)
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">{{ __('Ativa') }}</span>
                    @else
                        <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800 dark:bg-red-950/60 dark:text-red-300">{{ __('Inativa') }}</span>
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('platform.empresas.edit', $empresa) }}" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                    {{ __('Dados da empresa') }}
                </a>
                <a href="{{ route('platform.usuarios.index', ['empresa_id' => $empresa->id]) }}" class="inline-flex items-center rounded-lg bg-violet-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-violet-500">
                    {{ __('Utilizadores desta empresa') }}
                </a>
                <a href="{{ route('platform.usuarios.create', ['empresa_id' => $empresa->id]) }}" class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                    {{ __('Novo utilizador') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Utilizadores') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $empresa->users_count }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Papéis') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $empresa->roles_count }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Clientes') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $totClientes }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Processos') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $totProcessos }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Tipos proc.') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $totTiposProcesso }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Tipos doc.') }}</p>
                <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">{{ $totTiposDocumento }}</p>
            </div>
        </div>

        <div>
            <h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">{{ __('Equipa e permissões') }}</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('platform.empresas.admin.roles.index', $empresa) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-violet-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-violet-800">
                    <p class="text-sm font-semibold text-slate-900 group-hover:text-violet-700 dark:text-white dark:group-hover:text-violet-300">{{ __('Papéis e permissões') }}</p>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Defina quem pode ver e editar clientes, processos e cadastros.') }}</p>
                </a>
                <a href="{{ route('platform.usuarios.index', ['empresa_id' => $empresa->id]) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-violet-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-violet-800">
                    <p class="text-sm font-semibold text-slate-900 group-hover:text-violet-700 dark:text-white dark:group-hover:text-violet-300">{{ __('Utilizadores') }}</p>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Lista global filtrada por esta empresa; editar conta, bloquear ou suporte.') }}</p>
                </a>
            </div>
        </div>

        <div>
            <h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">{{ __('Processos e documentos') }}</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('platform.empresas.admin.tipo-processos.index', $empresa) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-800">
                    <p class="text-sm font-semibold text-slate-900 group-hover:text-indigo-700 dark:text-white dark:group-hover:text-indigo-300">{{ __('Tipos de processo') }}</p>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Fluxos, categorias e checklist por tipo.') }}</p>
                </a>
                <a href="{{ route('platform.empresas.admin.documento-tipos.index', $empresa) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-800">
                    <p class="text-sm font-semibold text-slate-900 group-hover:text-indigo-700 dark:text-white dark:group-hover:text-indigo-300">{{ __('Tipos de documento') }}</p>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Modelos e regras de documentos da empresa.') }}</p>
                </a>
                <a href="{{ route('platform.empresas.admin.documento-modelos.laboratorio', $empresa) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-800">
                    <p class="text-sm font-semibold text-slate-900 group-hover:text-indigo-700 dark:text-white dark:group-hover:text-indigo-300">{{ __('Documentos automatizados') }}</p>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Upload, mapeamento e modelos a partir do esqueleto global.') }}</p>
                </a>
            </div>
        </div>

        <div>
            <h3 class="mb-3 text-sm font-semibold text-slate-900 dark:text-white">{{ __('Operação e relatórios') }}</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('platform.empresas.admin.auditoria.index', $empresa) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-400 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Auditoria da empresa') }}</p>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Registo de ações nesta organização.') }}</p>
                </a>
                <a href="{{ route('platform.empresas.admin.relatorios.index', $empresa) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-400 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Relatórios') }}</p>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Processos e clientes por período; exportação CSV.') }}</p>
                </a>
            </div>
        </div>

        <p class="text-center text-xs text-slate-500 dark:text-slate-400">
            <a href="{{ route('platform.empresas.index') }}" class="font-medium text-violet-600 hover:text-violet-500 dark:text-violet-400">{{ __('← Voltar à lista de empresas') }}</a>
        </p>
    </div>
</x-platform-layout>
