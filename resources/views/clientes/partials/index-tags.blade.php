@php
    $hasAmbosTipos = is_array($tipos ?? []) && count(array_intersect($tipos, ['pf', 'pj'])) === 2;
    $tipoEfetivo = $hasAmbosTipos ? null : ((is_array($tipos ?? []) && count($tipos) === 1) ? $tipos[0] : null);
    $cidadeEfetiva = is_string($cidade ?? null) ? trim((string) $cidade) : '';
    $contatoEfetivo = is_string($contato ?? null) ? trim((string) $contato) : '';
    $nFiltros = 0;
    if ($tipoEfetivo) { $nFiltros++; }
    if ($cidadeEfetiva !== '') { $nFiltros++; }
    if ($contatoEfetivo !== '') { $nFiltros++; }
@endphp

<div>
    @if ($nFiltros > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if ($tipoEfetivo)
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-200 dark:ring-indigo-900/60"
                    @click="$dispatch('nx-clientes-remove-filter', { key: 'tipo' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ $tipoEfetivo === 'pf' ? __('Pessoa Física') : __('Pessoa Jurídica') }}
                    <span class="text-indigo-500 dark:text-indigo-300">×</span>
                </button>
            @endif

            @if ($cidadeEfetiva !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-clientes-remove-filter', { key: 'cidade' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ $cidadeEfetiva }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif

            @if ($contatoEfetivo !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-clientes-remove-filter', { key: 'contato' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Contato') }}: {{ $contatoEfetivo }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif
        </div>
    @endif
</div>

