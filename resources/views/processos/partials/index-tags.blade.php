@php
    use App\Enums\ProcessoStatus;
    use App\Enums\TipoProcessoCategoria;
    use App\Models\Habilitacao;

    $fa = $filtrosAvancados ?? [];
    $n = 0;

    $buscaEfetiva = is_string($busca ?? null) ? trim((string) $busca) : '';
    $statusEfetivo = is_string($statusFiltro ?? null) ? trim((string) $statusFiltro) : '';

    $categoriaFiltro = is_string($fa['cat'] ?? null) ? (string) ($fa['cat'] ?? '') : '';
    $tipoProcessoIdFiltro = (int) ($fa['tipo'] ?? 0);
    $jurisdicao = is_string($fa['jurisdicao'] ?? null) ? (string) ($fa['jurisdicao'] ?? '') : '';
    $clienteId = (int) ($fa['cliente'] ?? 0);
    $processoId = (int) ($fa['processo'] ?? 0);
    $docPendente = ! empty($fa['doc_pendente']);
    $de = is_string($fa['atualizado_de'] ?? null) ? (string) ($fa['atualizado_de'] ?? '') : '';
    $ate = is_string($fa['atualizado_ate'] ?? null) ? (string) ($fa['atualizado_ate'] ?? '') : '';
    if ($de !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $de)) {
        $de = \Carbon\Carbon::parse($de)->format('d/m/Y');
    }
    if ($ate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $ate)) {
        $ate = \Carbon\Carbon::parse($ate)->format('d/m/Y');
    }

    $categoriaLabel = '';
    if ($categoriaFiltro !== '') {
        try {
            $categoriaLabel = TipoProcessoCategoria::from($categoriaFiltro)->label();
        } catch (\ValueError) {
            $categoriaLabel = '';
        }
    }

    $statusLabel = '';
    if ($statusEfetivo !== '') {
        try {
            $statusLabel = ProcessoStatus::from($statusEfetivo)->label();
        } catch (\ValueError) {
            $statusLabel = '';
        }
    }

    $tipoProcessoLabel = '';
    if ($tipoProcessoIdFiltro > 0) {
        $tp = ($tiposProcessoModal ?? collect())->firstWhere('id', $tipoProcessoIdFiltro);
        $tipoProcessoLabel = $tp?->nome ?? '';
    }

    $clienteLabel = '';
    if ($clienteId > 0) {
        $row = ($clientesSuggestProcessoModal ?? collect())->firstWhere('id', $clienteId);
        $clienteLabel = is_array($row) ? (string) (($row['doc'] ?? '').' — '.($row['nome'] ?? '')) : '';
        $clienteLabel = trim($clienteLabel, " \t\n\r\0\x0B—");
    }

    $nFiltros = 0;
    foreach ([
        $buscaEfetiva !== '',
        $statusLabel !== '',
        $categoriaLabel !== '',
        $tipoProcessoLabel !== '',
        ($jurisdicao !== '' && in_array($jurisdicao, Habilitacao::JURISDICOES, true)),
        $clienteLabel !== '',
        $processoId > 0,
        $docPendente,
        $de !== '',
        $ate !== '',
    ] as $b) {
        if ($b) $nFiltros++;
    }
@endphp

<div>
    @if ($nFiltros > 0)
        <div class="flex flex-wrap items-center gap-2">
            @if ($buscaEfetiva !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-processos-remove-filter', { key: 'q' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Busca') }}: {{ $buscaEfetiva }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif

            @if ($statusLabel !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700 ring-1 ring-inset ring-violet-200 hover:bg-violet-100 dark:bg-violet-950/40 dark:text-violet-200 dark:ring-violet-900/60"
                    @click="$dispatch('nx-processos-remove-filter', { key: 'status' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ $statusLabel }}
                    <span class="text-violet-500 dark:text-violet-300">×</span>
                </button>
            @endif

            @if ($categoriaLabel !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-200 dark:ring-indigo-900/60"
                    @click="$dispatch('nx-processos-remove-filter', { key: 'cat' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Tipo de serviço') }}: {{ $categoriaLabel }}
                    <span class="text-indigo-500 dark:text-indigo-300">×</span>
                </button>
            @endif

            @if ($tipoProcessoLabel !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-processos-remove-filter', { key: 'tipo' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Tipo de processo') }}: {{ $tipoProcessoLabel }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif

            @if ($jurisdicao !== '' && in_array($jurisdicao, Habilitacao::JURISDICOES, true))
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-processos-remove-filter', { key: 'jurisdicao' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Jurisdição') }}: {{ $jurisdicao }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif

            @if ($clienteLabel !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-processos-remove-filter', { key: 'cliente' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Cliente') }}: {{ $clienteLabel }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif

            @if ($processoId > 0)
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-processos-remove-filter', { key: 'processo' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Processo') }}: #{{ $processoId }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif

            @if ($docPendente)
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-800 ring-1 ring-inset ring-orange-200 hover:bg-orange-100 dark:bg-orange-950/40 dark:text-orange-200 dark:ring-orange-900/60"
                    @click="$dispatch('nx-processos-remove-filter', { key: 'doc_pendente' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Documento pendente') }}
                    <span class="text-orange-500 dark:text-orange-300">×</span>
                </button>
            @endif

            @if ($de !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-processos-remove-filter', { key: 'atualizado_de' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Criado desde') }}: {{ $de }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif

            @if ($ate !== '')
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-100 dark:bg-slate-900/60 dark:text-slate-200 dark:ring-slate-800"
                    @click="$dispatch('nx-processos-remove-filter', { key: 'atualizado_ate' })"
                    title="{{ __('Remover filtro') }}"
                >
                    {{ __('Criado até') }}: {{ $ate }}
                    <span class="text-slate-400 dark:text-slate-500">×</span>
                </button>
            @endif
        </div>
    @endif
</div>

