<x-platform-layout :title="__('Empresas')">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Empresas') }}</h2>
            <a href="{{ route('platform.empresas.create') }}" class="inline-flex items-center rounded-lg bg-violet-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-violet-500">
                {{ __('Nova empresa') }}
            </a>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <form method="GET" action="{{ route('platform.empresas.index') }}" class="flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Busca') }}</label>
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('Nome, slug ou CNPJ…') }}" class="mt-1 min-w-[200px] rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white" />
            </div>
            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">{{ __('Filtrar') }}</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Nome') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('CNPJ') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Utilizadores') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Processos') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Estado') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Data de criação') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($empresas as $e)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">
                                <a href="{{ route('platform.empresas.show', $e) }}" class="text-violet-600 hover:text-violet-500 dark:text-violet-400">{{ $e->nome }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $e->cnpj ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm tabular-nums text-slate-700 dark:text-slate-300">{{ $e->users_count }}</td>
                            <td class="px-4 py-3 text-sm tabular-nums text-slate-700 dark:text-slate-300">{{ $e->processos_count }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if ($e->ativo)
                                    <span class="text-emerald-600 dark:text-emerald-400">{{ __('Ativo') }}</span>
                                @else
                                    <span class="text-red-600 dark:text-red-400">{{ __('Inativo') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm tabular-nums text-slate-600 dark:text-slate-300">{{ $e->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('platform.empresas.edit', $e) }}" class="font-medium text-violet-600 hover:text-violet-500 dark:text-violet-400">{{ __('Editar') }}</a>
                                    <a href="{{ route('platform.empresas.show', $e) }}" class="font-semibold text-violet-600 hover:text-violet-500 dark:text-violet-400">{{ __('Painel') }}</a>
                                    <a href="{{ route('platform.empresas.edit', $e) }}" class="font-medium text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200">{{ __('Dados') }}</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('Nenhuma empresa encontrada.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $empresas->links() }}</div>
    </div>
</x-platform-layout>
