<x-tenant-admin-layout title="{{ __('Clientes por período') }}">
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Clientes por período') }}</h2>
            <a href="{{ tenant_admin_route('relatorios.index') }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">{{ __('Voltar') }}</a>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl space-y-4">
            <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Início') }}</label>
                    <input type="text" name="inicio" value="{{ $inicio }}" inputmode="numeric" maxlength="10" autocomplete="off" placeholder="dd/mm/aaaa" data-nx-mask="date-br" class="mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Fim') }}</label>
                    <input type="text" name="fim" value="{{ $fim }}" inputmode="numeric" maxlength="10" autocomplete="off" placeholder="dd/mm/aaaa" data-nx-mask="date-br" class="mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                </div>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Filtrar') }}</button>
                <a href="{{ tenant_admin_route('relatorios.export.clientes', ['inicio' => $inicio, 'fim' => $fim]) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold dark:border-slate-600">{{ __('CSV') }}</a>
            </form>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('ID') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Criado') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Nome') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('CPF') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach ($clientes as $c)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 text-sm"><a href="{{ route('clientes.show', $c) }}" class="font-medium text-indigo-600 dark:text-indigo-400">#{{ $c->id }}</a></td>
                                <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">{{ $c->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-200">{{ $c->nome }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $c->cpf ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div>{{ $clientes->links() }}</div>
        </div>
    </div>
</x-tenant-admin-layout>
