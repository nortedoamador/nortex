@php
    $buscaTrim = trim((string) ($busca ?? ''));
    $qDataTrim = trim((string) ($qData ?? ''));
    $qNumeroTrim = trim((string) ($qNumero ?? ''));
    $qTipoTrim = trim((string) ($qTipoAula ?? ''));
    $qInstrutorTrim = trim((string) ($qInstrutor ?? ''));
    $qAlunoTrim = trim((string) ($qAluno ?? ''));

    $dataLabel = $qDataTrim;
    if ($qDataTrim !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $qDataTrim) === 1) {
        try {
            $dataLabel = \Carbon\Carbon::parse($qDataTrim)->format('d/m/Y');
        } catch (\Throwable) {
            $dataLabel = $qDataTrim;
        }
    }

    $tipoLabel = '';
    if ($qTipoTrim !== '') {
        $match = collect($tiposAula ?? [])->firstWhere('value', $qTipoTrim);
        $tipoLabel = is_array($match) ? (string) ($match['label'] ?? $qTipoTrim) : $qTipoTrim;
    }

    $nFiltros = 0;
    foreach ([$buscaTrim !== '', $qDataTrim !== '', $qNumeroTrim !== '', $qTipoTrim !== '', $qInstrutorTrim !== '', $qAlunoTrim !== ''] as $b) {
        if ($b) {
            $nFiltros++;
        }
    }
@endphp

<div>
    @if ($nFiltros > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if ($buscaTrim !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    data-nx-aulas-rm="q"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Busca') }}: {{ $buscaTrim }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif

            @if ($qDataTrim !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    data-nx-aulas-rm="data"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Data') }}: {{ $dataLabel }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif

            @if ($qNumeroTrim !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    data-nx-aulas-rm="numero_oficio"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Nº Ofício') }}: {{ $qNumeroTrim }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif

            @if ($qTipoTrim !== '' && $tipoLabel !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-200 dark:ring-indigo-900/60"
                    data-nx-aulas-rm="tipo_aula"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Tipo da aula') }}: {{ $tipoLabel }}
                    <span class="text-indigo-500 dark:text-indigo-300">×</span>
                </button>
            @endif

            @if ($qInstrutorTrim !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    data-nx-aulas-rm="instrutor"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Instrutor') }}: {{ $qInstrutorTrim }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif

            @if ($qAlunoTrim !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    data-nx-aulas-rm="aluno"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Aluno') }}: {{ $qAlunoTrim }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif
        </div>
    @endif
</div>
