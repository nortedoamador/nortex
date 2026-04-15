@php
    $tipoAtivo = is_string($tipo ?? null) ? trim((string) $tipo) : '';
    $atividadeAtiva = is_string($atividade ?? null) ? trim((string) $atividade) : '';
    $construtorAtivo = is_string($construtor ?? null) ? trim((string) $construtor) : '';
    $anoAtivo = is_string($anoConstrucao ?? null) ? trim((string) $anoConstrucao) : '';
    $motorAtivo = is_string($numeroMotor ?? null) ? trim((string) $numeroMotor) : '';
    $eInsc = (bool) ($embInsc ?? false);
    $eSin = (bool) ($embSin ?? false);
    $eAli = (bool) ($embAli ?? false);
    $eSal = (bool) ($embSal ?? false);
    $eVig = (bool) ($embVig ?? false);
    $eVen = (bool) ($embVen ?? false);
    $filtroInscAtivo = $eInsc !== $eSin;
    $filtroAliAtivo = $eAli !== $eSal;
    $filtroVigAtivo = $eVig !== $eVen;
    $nFiltros = 0;
    if ($tipoAtivo !== '') {
        $nFiltros++;
    }
    if ($atividadeAtiva !== '') {
        $nFiltros++;
    }
    if ($construtorAtivo !== '') {
        $nFiltros++;
    }
    if ($anoAtivo !== '') {
        $nFiltros++;
    }
    if ($motorAtivo !== '') {
        $nFiltros++;
    }
    if ($filtroInscAtivo) {
        $nFiltros++;
    }
    if ($filtroAliAtivo) {
        $nFiltros++;
    }
    if ($filtroVigAtivo) {
        $nFiltros++;
    }
@endphp

<div>
    @if ($nFiltros > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if ($tipoAtivo !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-200 dark:ring-indigo-900/60"
                    @click="$dispatch('nx-embarcacoes-remove-filter', { key: 'tipo' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Tipo') }}: {{ $tipoAtivo }}
                    <span class="text-indigo-500 dark:text-indigo-300">×</span>
                </button>
            @endif
            @if ($atividadeAtiva !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-200 dark:ring-indigo-900/60"
                    @click="$dispatch('nx-embarcacoes-remove-filter', { key: 'atividade' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Atividade') }}: {{ $atividadeAtiva }}
                    <span class="text-indigo-500 dark:text-indigo-300">×</span>
                </button>
            @endif
            @if ($construtorAtivo !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-embarcacoes-remove-filter', { key: 'construtor' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Construtor') }}: {{ $construtorAtivo }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif
            @if ($anoAtivo !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-embarcacoes-remove-filter', { key: 'ano_construcao' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Ano') }}: {{ $anoAtivo }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif
            @if ($motorAtivo !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-embarcacoes-remove-filter', { key: 'numero_motor' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Nº do motor') }}: {{ $motorAtivo }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif
            @if ($filtroInscAtivo)
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-embarcacoes-remove-filter', { key: 'emb_inscricao' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Inscrição') }}: {{ $eInsc ? __('Inscrita') : __('Sem inscrição') }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif
            @if ($filtroAliAtivo)
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-embarcacoes-remove-filter', { key: 'emb_alienacao' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Alienação') }}: {{ $eAli ? __('Com alienação') : __('Sem alienação') }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif
            @if ($filtroVigAtivo)
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-embarcacoes-remove-filter', { key: 'emb_vigencia' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Validade da inscrição') }}: {{ $eVig ? __('Em vigor') : __('Vencida') }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif
        </div>
    @endif
</div>
