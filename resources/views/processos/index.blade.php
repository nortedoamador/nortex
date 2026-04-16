@php
    use App\Enums\ProcessoStatus;

    $faQs = $filtrosAvancados ?? [];
    $qsProcessos = array_filter([
        'v' => $visualizacao ?? 'list',
        'q' => filled($busca ?? '') ? $busca : null,
        'status' => filled($statusFiltro ?? null) ? $statusFiltro : null,
        'tipo' => ($faQs['tipo'] ?? 0) > 0 ? (string) $faQs['tipo'] : null,
        'cat' => filled($faQs['cat'] ?? null) ? $faQs['cat'] : null,
        'jurisdicao' => filled($faQs['jurisdicao'] ?? null) ? $faQs['jurisdicao'] : null,
        'cliente' => ($faQs['cliente'] ?? 0) > 0 ? (string) $faQs['cliente'] : null,
        'processo' => ($faQs['processo'] ?? 0) > 0 ? (string) $faQs['processo'] : null,
        'doc_pendente' => ! empty($faQs['doc_pendente']) ? '1' : null,
        'atualizado_de' => filled($faQs['atualizado_de'] ?? null) ? $faQs['atualizado_de'] : null,
        'atualizado_ate' => filled($faQs['atualizado_ate'] ?? null) ? $faQs['atualizado_ate'] : null,
    ], fn ($v) => $v !== null && $v !== '');

    $nxTodosHex = '#475569';
    $nxFiltroTodosAtivo = ! filled($statusFiltro ?? null);
    $nxStFiltroAtual = (filled($statusFiltro ?? null) && is_string($statusFiltro))
        ? ProcessoStatus::tryFrom($statusFiltro)
        : null;

    $nxTituloSwalPendencias = __('Processo com pendências');
    $nxCienciaTextoSecundario = __('Deseja realmente alterar o status mesmo assim?');

    $nxBulkCfg = ($mostrarSelecaoEmLote ?? false) ? [
        'pageIds' => $idsSelecaoLotePagina ?? [],
        'deletableIds' => $idsExclusaoLotePagina ?? [],
        'podeAlterarStatus' => (bool) ($podeAlterarStatus ?? false),
        'podeExcluir' => (bool) ($podeExcluirLote ?? false),
        'statusLoteUrl' => route('processos.updateStatusMany'),
        'redirectV' => $visualizacao ?? 'list',
        'redirectQ' => filled($busca ?? '') ? $busca : null,
        'redirectStatus' => filled($statusFiltro ?? '') ? $statusFiltro : null,
        'redirectExtras' => array_filter([
            'redirect_tipo' => ($faQs['tipo'] ?? 0) > 0 ? (string) $faQs['tipo'] : null,
            'redirect_cat' => filled($faQs['cat'] ?? null) ? $faQs['cat'] : null,
            'redirect_jurisdicao' => filled($faQs['jurisdicao'] ?? null) ? $faQs['jurisdicao'] : null,
            'redirect_cliente' => ($faQs['cliente'] ?? 0) > 0 ? (string) $faQs['cliente'] : null,
            'redirect_processo' => ($faQs['processo'] ?? 0) > 0 ? (string) $faQs['processo'] : null,
            'redirect_doc_pendente' => ! empty($faQs['doc_pendente']) ? '1' : null,
            'redirect_atualizado_de' => filled($faQs['atualizado_de'] ?? null) ? $faQs['atualizado_de'] : null,
            'redirect_atualizado_ate' => filled($faQs['atualizado_ate'] ?? null) ? $faQs['atualizado_ate'] : null,
        ], fn ($v) => $v !== null && $v !== ''),
        'swalTitulo' => __('Excluir processos selecionados?'),
        'swalAviso' => __('Os anexos enviados serão removidos. Esta ação não pode ser desfeita.'),
        'msgLinha1' => __(':count processo selecionado para exclusão.'),
        'msgLinhaN' => __(':count processos selecionados para exclusão.'),
        'msgPergunta1' => __('Deseja realmente excluir este processo?'),
        'msgPerguntaN' => __('Deseja realmente excluir :count processos?'),
        'swalBtnNao' => __('Não, desistir'),
        'swalBtnSim' => __('Sim, excluir'),
        'cienciaTitulo' => $nxTituloSwalPendencias,
        'cienciaTextoSec' => $nxCienciaTextoSecundario,
        'cienciaLinhaLote' => __(':count processo(s) selecionado(s).'),
        'cienciaFraseLote' => __('Um ou mais processos selecionados têm documentos obrigatórios pendentes no checklist.'),
        'msgSemExclusaoLote' => __('Nenhum dos selecionados pode ser excluído em lote (só rascunhos em «Em montagem»).'),
        'msgErroLote' => __('Não foi possível concluir a ação. Tente de novo.'),
    ] : [];
@endphp

@inject('nxStatusSvc', \App\Services\ProcessoStatusService::class)

<x-app-layout title="{{ __('Processos') }}">
    <x-slot name="header">
        @php
            $nxProcV = $visualizacao ?? 'list';
            $nxProcTemFiltros = trim((string) ($busca ?? '')) !== ''
                || (is_string($statusFiltro ?? null) && $statusFiltro !== '')
                || (($filtrosAvancados['avancados_ativos'] ?? 0) > 0);
            if ($nxProcV === 'grid' && isset($processosGrid) && $processosGrid) {
                $nxProcTotalHeader = (int) $processosGrid->sum(fn ($c) => $c->count());
            } else {
                $nxProcTotalHeader = (int) ($processos?->total() ?? 0);
            }
            $nxProcCountTextHeader = $nxProcTemFiltros
                ? trans_choice('{0} Nenhum resultado|{1} :count resultado|[2,*] :count resultados', $nxProcTotalHeader, ['count' => $nxProcTotalHeader])
                : trans_choice('{0} Nenhum processo ativo|{1} :count processo ativo|[2,*] :count processos ativos', (int) $totalAtivos, ['count' => $totalAtivos]);
        @endphp
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5">
                    <h2 class="text-lg font-semibold tracking-tight text-slate-900 dark:text-white sm:text-xl">{{ __('Processos') }}</h2>
                    <span
                        id="nx-processos-count-badge"
                        class="inline-flex w-fit max-w-[200px] shrink-0 items-center justify-center truncate rounded-full border border-emerald-300/90 bg-brand-softer px-3 py-1.5 text-center text-xs font-semibold text-fg-brand-strong shadow-sm ring-1 ring-emerald-200/80 dark:border-emerald-700/80 dark:bg-brand-softer-dark dark:text-fg-brand-strong-dark dark:ring-emerald-800/80"
                    >{{ $nxProcCountTextHeader }}</span>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @include('processos.partials.index-filtros-panel', [
                    'visualizacao' => $visualizacao ?? 'list',
                    'busca' => $busca ?? '',
                    'statusFiltro' => $statusFiltro ?? null,
                    'filtrosAvancados' => $filtrosAvancados ?? [],
                    'tiposProcessoModal' => $tiposProcessoModal ?? collect(),
                    'clientesParaFiltroProcessos' => $clientesParaFiltroProcessos ?? collect(),
                ])
                <div
                    class="inline-flex rounded-full border border-slate-200 bg-slate-100/80 p-1 shadow-sm dark:border-slate-600 dark:bg-slate-800/80"
                    role="group"
                    aria-label="{{ __('Tipo de visualização') }}"
                >
                    <a
                        href="{{ route('processos.index', array_merge($qsProcessos, ['v' => 'list'])) }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full transition {{ ($visualizacao ?? 'list') === 'list' ? 'bg-white text-indigo-600 shadow dark:bg-slate-900 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}"
                        title="{{ __('Lista') }}"
                    >
                        <span class="sr-only">{{ __('Lista') }}</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 4.5h12M3.75 6.75h.008v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.008v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 4.5h.008v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </a>
                    <a
                        href="{{ route('processos.index', array_merge($qsProcessos, ['v' => 'grid'])) }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full transition {{ ($visualizacao ?? 'list') === 'grid' ? 'bg-white text-indigo-600 shadow dark:bg-slate-900 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}"
                        title="{{ __('Grade') }}"
                    >
                        <span class="sr-only">{{ __('Grade') }}</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 8.25V6ZM13.5 6.75h6v6h-6v-6Zm0 8.25h6v2.25A2.25 2.25 0 0 1 17.25 19.5h-2.25A2.25 2.25 0 0 1 12.75 17.25v-2.25Zm8.25-9v2.25A2.25 2.25 0 0 1 19.5 10.5h-6v-6h6A2.25 2.25 0 0 1 21.75 6ZM6 12.75h2.25A2.25 2.25 0 0 1 10.5 15v2.25A2.25 2.25 0 0 1 8.25 19.5H6a2.25 2.25 0 0 1-2.25-2.25V15A2.25 2.25 0 0 1 6 12.75Z" />
                        </svg>
                    </a>
                </div>
                @can('create', \App\Models\Processo::class)
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:focus:ring-indigo-400/30"
                        @click="$store.novoProcesso.preset = null; $store.novoProcesso.open = true"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('Novo processo') }}
                    </button>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div
            class="mx-auto space-y-6 {{ ($visualizacao ?? 'list') === 'grid' ? 'max-w-[1600px]' : 'max-w-4xl' }}"
            @if ($mostrarSelecaoEmLote)
                x-data="nxProcessosBulkActions({{ \Illuminate\Support\Js::from($nxBulkCfg) }})"
            @endif
        >
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif
            @if ($errors->has('status'))
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200">
                    {{ $errors->first('status') }}
                </div>
            @endif

            <div class="flex flex-col gap-3">
                <form id="nx-processos-filter-form" method="GET" action="{{ route('processos.index') }}" class="flex w-full min-w-0 flex-col gap-4">
                    <input type="hidden" name="v" value="{{ $visualizacao ?? 'list' }}" />
                    @if (filled($statusFiltro ?? null))
                        <input type="hidden" name="status" value="{{ $statusFiltro }}" />
                    @endif
                    @include('processos.partials.index-filtros-hidden', ['filtrosAvancados' => $filtrosAvancados ?? []])
                    <button type="submit" class="sr-only" tabindex="-1">{{ __('Aplicar busca') }}</button>
                    <div class="flex w-full min-w-0 flex-col gap-3 sm:flex-row sm:items-stretch sm:gap-3">
                        <label class="relative flex min-h-12 min-w-0 flex-1">
                            <span class="sr-only">{{ __('Buscar processos') }}</span>
                            <span class="pointer-events-none absolute inset-y-0 left-4 z-10 flex items-center text-slate-400 dark:text-slate-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </span>
                            <input
                                type="search"
                                name="q"
                                value="{{ $busca }}"
                                placeholder="{{ __('Buscar processos...') }}"
                                autocomplete="off"
                                data-nx-processos-auto="1"
                                class="box-border h-12 w-full rounded-full border border-slate-200 bg-white py-0 pl-12 pr-4 text-sm leading-normal text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                            />
                        </label>
                        <div
                            class="flex w-full min-w-0 shrink-0 sm:w-auto sm:min-w-[18rem] sm:max-w-[min(100%,34rem)]"
                            x-data="{ open: false }"
                            @keydown.escape.window="open = false"
                            @click.outside="open = false"
                        >
                            <div class="flex min-w-0 w-full items-stretch gap-2">
                                <span
                                    class="inline-flex h-12 w-11 shrink-0 items-center justify-center self-stretch rounded-lg border border-slate-200 bg-slate-50 text-slate-500 shadow-sm dark:border-slate-600 dark:bg-slate-800/80 dark:text-slate-400"
                                    title="{{ __('Filtrar por status') }}"
                                    aria-hidden="true"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                                    </svg>
                                </span>
                                <div class="relative min-h-12 min-w-0 flex-1">
                            <label id="nx-processo-status-filter-label" class="sr-only">{{ __('Filtrar por status') }}</label>
                            <button
                                type="button"
                                id="nx-processo-status-filter-trigger"
                                class="flex h-12 w-full items-center justify-between gap-3 rounded-lg border px-4 text-left text-sm font-medium shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/25 dark:focus-visible:ring-indigo-400/30 {{ $nxFiltroTodosAtivo ? 'border-violet-200 bg-violet-50 dark:border-violet-800/50 dark:bg-violet-950/35' : 'border-slate-200 bg-white dark:border-slate-600 dark:bg-slate-900' }}"
                                :class="open ? 'ring-2 ring-indigo-500/15 ring-offset-1 ring-offset-white dark:ring-offset-slate-950' : ''"
                                :aria-expanded="open"
                                aria-haspopup="listbox"
                                aria-labelledby="nx-processo-status-filter-label"
                                @click="open = ! open"
                            >
                                <span class="flex min-w-0 flex-1 items-center gap-2.5">
                                    @if ($nxStFiltroAtual instanceof ProcessoStatus)
                                        @php $nxTrgHex = $nxStFiltroAtual->uiBrandHex(); @endphp
                                        <span class="nx-processo-status-row__icon shrink-0" style="color: {{ $nxTrgHex }}">
                                            @include('processos.partials.status-filter-icon', ['status' => $nxStFiltroAtual])
                                        </span>
                                        <span id="nx-processo-status-trigger-text" class="min-w-0 truncate text-slate-800 dark:text-slate-100">{{ $nxStFiltroAtual->label() }}</span>
                                    @else
                                        <span id="nx-processo-status-trigger-text" class="min-w-0 truncate text-violet-700 dark:text-violet-300">{{ __('Todos os status') }}</span>
                                    @endif
                                </span>
                                <svg
                                    class="h-4 w-4 shrink-0 text-slate-400 transition-transform dark:text-slate-500"
                                    :class="open ? 'rotate-180' : ''"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <div
                                x-show="open"
                                x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 translate-y-0.5"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-0.5"
                                class="absolute left-0 top-full z-50 mt-1 w-max min-w-full max-w-[min(calc(100vw-1.5rem),32rem)]"
                                role="presentation"
                            >
                                <div
                                    class="nx-processo-status-shell nx-processo-status-dropdown-scroll shadow-lg dark:shadow-black/30"
                                    role="listbox"
                                    aria-labelledby="nx-processo-status-filter-label"
                                >
                                    <div class="flex flex-col divide-y divide-slate-100 dark:divide-slate-700/60">
                                        <button
                                            type="button"
                                            @if ($nxFiltroTodosAtivo) aria-current="true" @endif
                                            aria-selected="{{ $nxFiltroTodosAtivo ? 'true' : 'false' }}"
                                            @click="
                                                const f = document.getElementById('nx-processos-filter-form');
                                                if (f) {
                                                    let h = f.querySelector('[name=\'status\']');
                                                    if (!h) { h = document.createElement('input'); h.type = 'hidden'; h.name = 'status'; f.appendChild(h); }
                                                    h.value = '';
                                                }
                                                const t = document.getElementById('nx-processo-status-trigger-text');
                                                if (t) { t.textContent = '{{ __('Todos os status') }}'; }
                                                open = false;
                                                window.nxProcessosIndexApply && window.nxProcessosIndexApply();
                                            "
                                            class="nx-processo-status-row nx-processo-status-row--todos {{ $nxFiltroTodosAtivo ? 'nx-processo-status-row--todos-active' : '' }} cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-indigo-500/25"
                                            style="--nx-pill: {{ $nxFiltroTodosAtivo ? '#9B51E0' : '#94a3b8' }};"
                                            role="option"
                                        >
                                            <span class="nx-processo-status-row__label nx-processo-status-row__label--todos whitespace-nowrap text-[0.9375rem] font-medium leading-snug">{{ __('Todos os status') }}</span>
                                        </button>
                                        @foreach ($etapas ?? [] as $nxSt)
                                            @php
                                                $nxStHex = $nxSt->uiBrandHex();
                                                $nxStAtivo = ($statusFiltro ?? '') === $nxSt->value;
                                            @endphp
                                            <button
                                                type="button"
                                                @if ($nxStAtivo) aria-current="true" @endif
                                                aria-selected="{{ $nxStAtivo ? 'true' : 'false' }}"
                                                @click="
                                                    const f = document.getElementById('nx-processos-filter-form');
                                                    if (f) {
                                                        let h = f.querySelector('[name=\'status\']');
                                                        if (!h) { h = document.createElement('input'); h.type = 'hidden'; h.name = 'status'; f.appendChild(h); }
                                                        h.value = '{{ $nxSt->value }}';
                                                    }
                                                    const t = document.getElementById('nx-processo-status-trigger-text');
                                                    if (t) { t.textContent = '{{ $nxSt->label() }}'; }
                                                    open = false;
                                                    window.nxProcessosIndexApply && window.nxProcessosIndexApply();
                                                "
                                                class="nx-processo-status-row cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-indigo-500/25"
                                                style="--nx-pill: {{ $nxStHex }};"
                                                role="option"
                                            >
                                                <span class="nx-processo-status-row__icon" style="color: {{ $nxStHex }}">
                                                    @include('processos.partials.status-filter-icon', ['status' => $nxSt])
                                                </span>
                                                <span class="whitespace-nowrap text-[0.9375rem] font-medium leading-snug text-slate-800 dark:text-slate-100">{{ $nxSt->label() }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                                @if (($busca ?? '') !== '' || ($statusFiltro ?? '') !== '' || (($filtrosAvancados['avancados_ativos'] ?? 0) > 0))
                                    <button
                                        type="button"
                                        data-nx-processos-reset="1"
                                        class="inline-flex h-12 shrink-0 cursor-pointer items-center justify-center self-stretch rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/25 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-400 dark:hover:bg-slate-800"
                                    >{{ __('Limpar') }}</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>

                <div
                    id="nx-processos-tags"
                    class="min-h-[1px]"
                >
                    @include('processos.partials.index-tags', [
                        'busca' => $busca,
                        'statusFiltro' => $statusFiltro,
                        'filtrosAvancados' => $filtrosAvancados ?? [],
                        'tiposProcessoModal' => $tiposProcessoModal ?? collect(),
                        'clientesSuggestProcessoModal' => $clientesSuggestProcessoModal ?? collect(),
                    ])
                </div>
                @if ($mostrarSelecaoEmLote)
                    <div
                        class="flex w-full min-w-0 flex-col gap-2 border-t border-slate-200 pt-3 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-3 sm:gap-y-2 dark:border-slate-700"
                        x-show="count > 0"
                        x-cloak
                    >
                        <span class="shrink-0 whitespace-nowrap text-sm font-semibold text-slate-700 dark:text-slate-200">
                            <span x-show="count === 1">{{ __('1 selecionado') }}</span>
                            <span x-show="count > 1"><span x-text="count"></span> {{ __('selecionados') }}</span>
                        </span>
                        <label class="inline-flex shrink-0 cursor-pointer items-center gap-2 whitespace-nowrap text-sm font-medium text-slate-700 dark:text-slate-300">
                            <input
                                type="checkbox"
                                class="h-4 w-4 shrink-0 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                                :checked="allOnPageSelected"
                                @change="toggleAllOnPage($event.target.checked)"
                            />
                            <span>{{ __('Marcar todos') }}</span>
                        </label>
                        @if ($podeAlterarStatus)
                            <div class="flex min-w-0 flex-wrap items-center gap-2">
                                <label class="sr-only" for="nx_bulk_status_destino">{{ __('Novo status para os selecionados') }}</label>
                                <select
                                    id="nx_bulk_status_destino"
                                    x-model="statusDestino"
                                    class="min-w-[10rem] max-w-[14rem] rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-8 text-sm font-medium text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                >
                                    <option value="">{{ __('Novo status…') }}</option>
                                    @foreach ($etapas as $opt)
                                        <option value="{{ $opt->value }}" style="{{ $opt->uiNativeSelectOptionStyle() }}">{{ $opt->label() }}</option>
                                    @endforeach
                                </select>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center gap-2 rounded-full bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
                                    @click="aplicarEtapaLote()"
                                    :disabled="!statusDestino || count === 0"
                                >
                                    {{ __('Aplicar status') }}
                                </button>
                                <a href="{{ route('processos.kanban') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Kanban') }}</a>
                            </div>
                        @endif
                        @if ($podeExcluirLote)
                            <button
                                type="button"
                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-red-200 bg-red-50 text-red-700 shadow-sm transition hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200 dark:hover:bg-red-950/60"
                                title="{{ __('Excluir selecionados') }}"
                                aria-label="{{ __('Excluir selecionados') }}"
                                @click="excluirLote()"
                                :disabled="countDeletable === 0"
                            >
                                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        @endif
                        <button
                            type="button"
                            class="inline-flex shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="limparSelecao()"
                        >
                            {{ __('Limpar seleção') }}
                        </button>
                    </div>
                @endif
            </div>

            @if ($mostrarSelecaoEmLote)
                <form id="nx-processos-bulk-delete-form" method="POST" action="{{ route('processos.destroyMany') }}" class="hidden">
                    @csrf
                    <input type="hidden" name="redirect_v" value="{{ $visualizacao ?? 'list' }}" />
                    @if (filled($busca ?? ''))
                        <input type="hidden" name="redirect_q" value="{{ $busca }}" />
                    @endif
                    @if (filled($statusFiltro ?? ''))
                        <input type="hidden" name="redirect_status" value="{{ $statusFiltro }}" />
                    @endif
                    @include('processos.partials.index-redirect-fields', ['filtrosAvancados' => $filtrosAvancados ?? []])
                </form>
            @endif

            <div id="nx-processos-list">
                @include('processos.partials.index-list')
            </div>

            <div
                id="nx-processos-pagination"
                class="min-h-[1px]"
                @click.prevent="
                    const a = $event.target.closest('a');
                    if (a && a.href) { window.nxProcessosIndexApply && window.nxProcessosIndexApply(a.href); }
                "
            >
                @if (($visualizacao ?? 'list') === 'list' && $processos && $processos->hasPages())
                    <div>{{ $processos->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    @can('create', \App\Models\Processo::class)
        <div class="hidden" x-data x-init="@if ($errors->any()) $store.novoProcesso.preset = null; $store.novoProcesso.open = true @endif"></div>
        <div class="hidden" x-data x-init="if (new URLSearchParams(location.search).get('abrir_novo') === '1') { $store.novoProcesso.preset = null; $store.novoProcesso.open = true }"></div>
        @include('processos.partials.modal-novo-processo', [
            'tipos' => $tiposProcessoModal,
            'clientesSuggest' => $clientesSuggestProcessoModal,
        ])
    @endcan
</x-app-layout>

<script>
    (function () {
        const debounce = (fn, ms) => {
            let t = null;
            return (...args) => {
                if (t) clearTimeout(t);
                t = setTimeout(() => fn(...args), ms);
            };
        };

        const formToUrl = (form) => {
            const url = new URL(form.action, window.location.origin);
            const fd = new FormData(form);
            for (const [k, v] of fd.entries()) {
                if (v == null) continue;
                const s = String(v);
                if (s === '') continue;
                if (k === 'v' && s === 'list') continue;
                url.searchParams.append(k, s);
            }
            return url;
        };

        const syncAdvancedToMain = () => {
            const main = document.getElementById('nx-processos-filter-form');
            const adv = document.getElementById('nx-processos-advanced-form');
            if (!main || !adv) return;
            const fd = new FormData(adv);

            // campos de filtros avançados (não sobrescreve busca/status do form principal)
            const keys = [
                'cat',
                'tipo',
                'jurisdicao',
                'cliente',
                'processo',
                'doc_pendente',
                'atualizado_de',
                'atualizado_ate',
            ];

            keys.forEach((k) => {
                const val = fd.get(k);
                let el = main.querySelector(`[name="${k}"]`);
                if (!el) {
                    el = document.createElement('input');
                    el.type = 'hidden';
                    el.name = k;
                    main.appendChild(el);
                }
                if (el.type === 'checkbox') {
                    el.checked = String(val || '') === '1';
                } else {
                    el.value = val == null ? '' : String(val);
                }
            });
        };

        const syncInputsFromUrl = (form, url) => {
            const params = url.searchParams;
            const inputs = form.querySelectorAll('input[name], select[name], textarea[name]');
            inputs.forEach((el) => {
                const name = el.getAttribute('name');
                if (!name) return;
                const val = params.get(name) ?? '';
                if (el.type === 'checkbox') {
                    el.checked = val === '1' || val === 'on' || val === 'true';
                    return;
                }
                if (el.tagName === 'SELECT') {
                    el.value = val;
                    return;
                }
                if (el instanceof HTMLInputElement && el.dataset.nxMask === 'date-br' && /^\d{4}-\d{2}-\d{2}$/.test(val)) {
                    el.value = `${val.slice(8, 10)}/${val.slice(5, 7)}/${val.slice(0, 4)}`;
                    return;
                }
                el.value = val;
            });
        };

        const resetAdvancedUiExtras = () => {
            // Campos visuais do filtro cliente (não tem name, então não entra no syncInputsFromUrl)
            const q = document.getElementById('nx_f_cliente_q');
            if (q) q.value = '';
        };

        const clearFilters = () => {
            const main = document.getElementById('nx-processos-filter-form');
            const adv = document.getElementById('nx-processos-advanced-form');
            if (main) {
                // limpa busca/status no form principal
                const q = main.querySelector('[name="q"]');
                if (q) q.value = '';
                const st = main.querySelector('[name="status"]');
                if (st) st.value = '';

                // limpa hidden inputs avançados que vivem no form principal
                [
                    'cat',
                    'tipo',
                    'jurisdicao',
                    'cliente',
                    'processo',
                    'doc_pendente',
                    'atualizado_de',
                    'atualizado_ate',
                ].forEach((k) => {
                    const el = main.querySelector(`[name="${k}"]`);
                    if (!el) return;
                    if (el.type === 'checkbox') el.checked = false;
                    else el.value = '';
                });
            }
            if (adv) {
                // limpa valores do painel (para não sobrescrever no syncAdvancedToMain)
                [
                    'cat',
                    'tipo',
                    'jurisdicao',
                    'cliente',
                    'processo',
                    'doc_pendente',
                    'atualizado_de',
                    'atualizado_ate',
                ].forEach((k) => {
                    const el = adv.querySelector(`[name="${k}"]`);
                    if (!el) return;
                    if (el.type === 'checkbox') el.checked = false;
                    else el.value = '';
                });
            }
            resetAdvancedUiExtras();
        };

        const apply = async (href = null) => {
            const form = document.getElementById('nx-processos-filter-form');
            if (!form) return;
            syncAdvancedToMain();
            const url = href ? new URL(href) : formToUrl(form);
            if (!href) url.searchParams.delete('page');

            const qs = url.searchParams.toString();
            history.replaceState({}, '', qs ? `${url.pathname}?${qs}` : url.pathname);

            try {
                const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();

                const tags = document.getElementById('nx-processos-tags');
                const list = document.getElementById('nx-processos-list');
                const pag = document.getElementById('nx-processos-pagination');
                if (tags && data.tags_html != null) tags.innerHTML = data.tags_html;
                if (list && data.list_html != null) list.innerHTML = data.list_html;
                if (pag) pag.innerHTML = data.pagination_html ?? '';

                const badge = document.getElementById('nx-processos-filtros-badge');
                if (badge && data.avancados_ativos != null) {
                    const n = Number(data.avancados_ativos) || 0;
                    badge.textContent = String(n);
                    badge.classList.toggle('hidden', n <= 0);
                }

                const countBadge = document.getElementById('nx-processos-count-badge');
                if (countBadge && data.count_text != null) {
                    countBadge.textContent = String(data.count_text);
                }

                // Mantém forms auxiliares sincronizados (painel avançado e afins)
                document.querySelectorAll('form[action$="/processos"], form[action*="processos.index"]').forEach((f) => {
                    try { syncInputsFromUrl(f, url); } catch (_) {}
                });
            } catch (e) {
                // ignore
            }
        };

        window.nxProcessosIndexApply = apply;
        window.nxProcessosClearFilters = clearFilters;

        const boot = () => {
            const form = document.getElementById('nx-processos-filter-form');
            if (!form) return;

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                apply();
            });

            const autoApply = debounce(() => apply(), 250);

            document.addEventListener('input', (e) => {
                const t = e.target;
                if (!(t instanceof HTMLElement)) return;
                if (t.closest('#nx-processos-filter-form') || t.closest('#nx-processos-filtros-titulo') || t.closest('[role=\"dialog\"]')) {
                    if (t.matches('[data-nx-processos-auto]')) autoApply();
                }
            });

            document.addEventListener('change', (e) => {
                const t = e.target;
                if (!(t instanceof HTMLElement)) return;
                if (t.closest('form') && t.closest('form').querySelector('input[name=\"v\"][value]')) {
                    autoApply();
                }
            });

            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-nx-processos-reset=\"1\"]');
                if (!btn) return;
                e.preventDefault();
                clearFilters();
                apply();
            });

            window.addEventListener('nx-processos-remove-filter', (ev) => {
                const key = ev.detail && ev.detail.key ? String(ev.detail.key) : '';
                if (!key) return;
                const map = {
                    q: 'q',
                    status: 'status',
                    cat: 'cat',
                    tipo: 'tipo',
                    jurisdicao: 'jurisdicao',
                    cliente: 'cliente',
                    processo: 'processo',
                    doc_pendente: 'doc_pendente',
                    atualizado_de: 'atualizado_de',
                    atualizado_ate: 'atualizado_ate',
                };
                const name = map[key];
                if (!name) return;
                const el = form.querySelector(`[name=\"${name}\"]`);
                if (el) {
                    if (el.type === 'checkbox') el.checked = false;
                    else el.value = '';
                }
                const adv = document.getElementById('nx-processos-advanced-form');
                if (adv) {
                    const el2 = adv.querySelector(`[name=\"${name}\"]`);
                    if (el2) {
                        if (el2.type === 'checkbox') el2.checked = false;
                        else el2.value = '';
                    }
                }
                if (name === 'cliente') {
                    resetAdvancedUiExtras();
                }
                apply();
            });
        };

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
        else boot();
    })();
</script>
