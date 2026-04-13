<x-tenant-admin-layout title="{{ __('Tipos de processo') }}">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Tipos de processo') }}</h2>
            <a href="{{ tenant_admin_route('tipo-processos.create') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-500">
                {{ __('Novo tipo') }}
            </a>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl space-y-4">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</div>
            @endif
            @if ($errors->has('delete'))
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">{{ $errors->first('delete') }}</div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Nome') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Categoria') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Processos') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-600 dark:text-slate-400"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach ($tipos as $t)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $t->nome }}<br><code class="text-xs text-slate-500">{{ $t->slug }}</code></td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $t->categoria->label() }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $t->processos_count }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm space-x-2">
                                    <a href="{{ tenant_admin_route('tipo-processos.edit-regras', $t) }}" class="font-medium text-violet-600 hover:text-violet-500 dark:text-violet-400">{{ __('Checklist') }}</a>
                                    <a href="{{ tenant_admin_route('tipo-processos.edit', $t) }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Editar') }}</a>
                                    @if ($t->processos_count === 0)
                                        <form method="POST" action="{{ tenant_admin_route('tipo-processos.destroy', $t) }}" class="inline" onsubmit="return confirm(@json(__('Tem certeza?')));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-medium text-red-600 hover:text-red-500 dark:text-red-400">{{ __('Remover') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-tenant-admin-layout>
