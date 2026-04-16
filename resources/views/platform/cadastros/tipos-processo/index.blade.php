<x-platform-layout :title="__('Tipos de processo (global)')">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Tipos de processo (global)') }}</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Cadastros globais da plataforma (valem para todas as empresas).') }}</p>
            </div>
            <a href="{{ route('platform.cadastros.tipos-processo.create') }}" class="inline-flex items-center rounded-lg bg-violet-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-violet-500">
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
        @if ($errors->has('bulk'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">
                {{ $errors->first('bulk') }}
            </div>
        @endif

        <form method="GET" action="{{ route('platform.cadastros.tipos-processo.index') }}" class="flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Busca') }}</label>
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('Nome, slug ou categoria…') }}" class="mt-1 min-w-[240px] rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white" />
            </div>
            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">{{ __('Filtrar') }}</button>
        </form>

        <form
            method="POST"
            action="{{ route('platform.cadastros.tipos-processo.bulk') }}"
            class="space-y-3"
            onsubmit="
                const action = this.querySelector('[name=action]')?.value;
                if (action === 'delete_selected') {
                    return confirm(@js(__('Excluir permanentemente os itens selecionados? Esta ação não pode ser desfeita.')));
                }
                if (action === 'activate_all') {
                    return confirm(@js(__('Ativar todos os itens desta lista (respeitando o filtro atual)?')));
                }
                if (action === 'deactivate_all') {
                    return confirm(@js(__('Desativar todos os itens desta lista (respeitando o filtro atual)?')));
                }
                return true;
            "
        >
            @csrf
            <input type="hidden" name="q" value="{{ $q }}" />

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <label class="text-xs font-semibold uppercase tracking-widest text-slate-600 dark:text-slate-400">{{ __('Ações em massa') }}</label>
                    <select name="action" class="rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        <option value="activate_selected">{{ __('Ativar selecionados') }}</option>
                        <option value="deactivate_selected">{{ __('Desativar selecionados') }}</option>
                        <option value="delete_selected">{{ __('Excluir permanentemente selecionados') }}</option>
                        <option value="activate_all">{{ __('Ativar todos (da lista)') }}</option>
                        <option value="deactivate_all">{{ __('Desativar todos (da lista)') }}</option>
                    </select>
                    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">
                        {{ __('Aplicar') }}
                    </button>

                    <span id="nx-selected-count" class="text-xs text-slate-500 dark:text-slate-400 hidden">
                        <span class="font-semibold" data-count>0</span> {{ __('selecionado(s)') }}
                    </span>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="w-10 px-4 py-3 text-left">
                                <label class="inline-flex items-center gap-2">
                                    <input id="nx-select-all" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500 dark:border-slate-600 dark:bg-slate-900" />
                                    <span class="sr-only">{{ __('Selecionar todos') }}</span>
                                </label>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Nome') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Slug') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Categoria') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Estado') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-600 dark:text-slate-400"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse ($tipos as $t)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3">
                                    <input
                                        type="checkbox"
                                        name="ids[]"
                                        value="{{ $t->id }}"
                                        class="nx-row-check h-4 w-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500 dark:border-slate-600 dark:bg-slate-900"
                                    />
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $t->nome }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $t->slug }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                    @php
                                        $cat = $t->categoria instanceof \App\Enums\TipoProcessoCategoria
                                            ? $t->categoria
                                            : ($t->categoria ? \App\Enums\TipoProcessoCategoria::tryFrom((string) $t->categoria) : null);
                                    @endphp
                                    {{ $cat?->label() ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($t->ativo)
                                        <span class="text-emerald-600 dark:text-emerald-400">{{ __('Ativo') }}</span>
                                    @else
                                        <span class="text-red-600 dark:text-red-400">{{ __('Inativo') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <a href="{{ route('platform.cadastros.tipos-processo.edit', $t) }}" class="font-medium text-violet-600 hover:text-violet-500 dark:text-violet-400">{{ __('Editar') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('Nenhum tipo encontrado.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <script>
            (function () {
                const selectAll = document.getElementById('nx-select-all');
                const checks = Array.from(document.querySelectorAll('.nx-row-check'));
                const countWrap = document.getElementById('nx-selected-count');
                const countEl = countWrap ? countWrap.querySelector('[data-count]') : null;

                function updateCount() {
                    const n = checks.filter((c) => c.checked).length;
                    if (countEl) countEl.textContent = String(n);
                    if (countWrap) countWrap.classList.toggle('hidden', n === 0);
                    if (selectAll) selectAll.checked = n > 0 && n === checks.length;
                    if (selectAll) selectAll.indeterminate = n > 0 && n < checks.length;
                }

                if (selectAll) {
                    selectAll.addEventListener('change', () => {
                        checks.forEach((c) => { c.checked = selectAll.checked; });
                        updateCount();
                    });
                }

                checks.forEach((c) => c.addEventListener('change', updateCount));
                updateCount();
            })();
        </script>

    </div>
</x-platform-layout>

