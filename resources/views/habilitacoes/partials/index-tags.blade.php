@php
    $clienteEfetivo = is_string($clienteBusca ?? null) ? trim((string) $clienteBusca) : '';
    $categoriaEfetiva = is_string($categoria ?? null) ? trim((string) $categoria) : '';
    $jurisdicaoEfetiva = is_string($jurisdicao ?? null) ? trim((string) $jurisdicao) : '';
    $vigenciaEfetiva = is_string($vigencia ?? null) ? trim((string) $vigencia) : '';
    $nFiltros = 0;
    if ($clienteEfetivo !== '') {
        $nFiltros++;
    }
    if ($categoriaEfetiva !== '') {
        $nFiltros++;
    }
    if ($jurisdicaoEfetiva !== '') {
        $nFiltros++;
    }
    if ($vigenciaEfetiva !== '') {
        $nFiltros++;
    }
@endphp

<div>
    @if ($nFiltros > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if ($clienteEfetivo !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-habilitacoes-remove-filter', { key: 'cliente' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Cliente') }}: {{ $clienteEfetivo }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif
            @if ($categoriaEfetiva !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-200 dark:ring-indigo-900/60"
                    @click="$dispatch('nx-habilitacoes-remove-filter', { key: 'categoria' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ $categoriaEfetiva }}
                    <span class="text-indigo-500 dark:text-indigo-300">×</span>
                </button>
            @endif
            @if ($jurisdicaoEfetiva !== '')
                <button
                    type="button"
                    class="inline-flex max-w-full items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-200 dark:ring-indigo-900/60"
                    @click="$dispatch('nx-habilitacoes-remove-filter', { key: 'jurisdicao' })"
                    title="{{ __('Remover filtro') }}"
                >
                    <span class="truncate">{{ $jurisdicaoEfetiva }}</span>
                    <span class="shrink-0 text-indigo-500 dark:text-indigo-300">×</span>
                </button>
            @endif
            @if ($vigenciaEfetiva !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-200 dark:ring-indigo-900/60"
                    @click="$dispatch('nx-habilitacoes-remove-filter', { key: 'vigencia' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ $vigenciaEfetiva === 'em_vigor' ? __('Em vigor') : __('Vencida') }}
                    <span class="text-indigo-500 dark:text-indigo-300">×</span>
                </button>
            @endif
        </div>
    @endif
</div>
