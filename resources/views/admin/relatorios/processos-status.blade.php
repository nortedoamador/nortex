<x-tenant-admin-layout title="{{ __('Processos por status') }}">
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Processos por status') }}</h2>
            <a href="{{ tenant_admin_route('relatorios.index') }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Voltar') }}</a>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @foreach ($linhas as $linha)
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">{{ $linha['status']->label() }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-slate-900 dark:text-white">{{ $linha['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-tenant-admin-layout>
