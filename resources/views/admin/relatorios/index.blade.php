<x-tenant-admin-layout title="{{ __('Relatórios') }}">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Relatórios') }}</h2>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl space-y-3">
            <a href="{{ tenant_admin_route('relatorios.processos-status') }}" class="block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-900/50">
                <p class="font-semibold text-slate-900 dark:text-white">{{ __('Processos por status') }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Contagem atual por etapa do fluxo.') }}</p>
            </a>
            <a href="{{ tenant_admin_route('relatorios.processos-periodo') }}" class="block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-900/50">
                <p class="font-semibold text-slate-900 dark:text-white">{{ __('Processos por período') }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Lista e exportação CSV.') }}</p>
            </a>
            <a href="{{ tenant_admin_route('relatorios.clientes-periodo') }}" class="block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-900/50">
                <p class="font-semibold text-slate-900 dark:text-white">{{ __('Clientes por período') }}</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Lista e exportação CSV.') }}</p>
            </a>
        </div>
    </div>
</x-tenant-admin-layout>
