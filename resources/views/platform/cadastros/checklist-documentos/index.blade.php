<x-platform-layout :title="__('Checklist de documentos (global)')">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Checklist de documentos (global)') }}</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Tipos de documento da empresa de referência «:e» (ID :id). Usados nos checklists dos tipos de processo globais.', ['e' => $checklistEmpresa->nome, 'id' => $checklistEmpresa->id]) }}
                </p>
            </div>
            <a href="{{ route('platform.cadastros.checklist-documentos.create') }}" class="inline-flex shrink-0 items-center rounded-lg bg-violet-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-violet-500">
                {{ __('Novo tipo') }}
            </a>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->has('delete'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">{{ $errors->first('delete') }}</div>
        @endif

        <form method="GET" action="{{ route('platform.cadastros.checklist-documentos.index') }}" class="flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Busca') }}</label>
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('Código, nome ou slug…') }}" class="mt-1 min-w-[240px] rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white" />
            </div>
            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">{{ __('Filtrar') }}</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Código') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Nome') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Modelo (slug)') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Auto') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-600 dark:text-slate-400"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($tipos as $t)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 font-mono text-sm text-slate-700 dark:text-slate-300">{{ $t->codigo }}</td>
                            <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">{{ $t->nome }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-400">{{ $t->modelo_slug ?: '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $t->auto_gerado ? __('Sim') : __('Não') }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                <a href="{{ route('platform.cadastros.checklist-documentos.edit', $t->id) }}" class="font-medium text-violet-600 hover:text-violet-500 dark:text-violet-400">{{ __('Editar') }}</a>
                                <form method="POST" action="{{ route('platform.cadastros.checklist-documentos.destroy', $t->id) }}" class="ms-3 inline" onsubmit="return confirm(@json(__('Tem certeza?')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:text-red-500 dark:text-red-400">{{ __('Remover') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Nenhum tipo encontrado.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-platform-layout>
