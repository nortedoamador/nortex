@inject('nxStatusSvc', \App\Services\ProcessoStatusService::class)
@php
    $nxTituloSwalPendencias = __('Processo com pendências');
    $nxCienciaTextoSecundario = __('Deseja realmente alterar o status mesmo assim?');
    $nxLinhaFicha = ($processo->tipoProcesso?->nome ?? __('Processo'));
    $nxNPendCiencia = $nxStatusSvc->quantidadeDocumentosObrigatoriosPendentes($processo);
    $nxFraseCiencia = trans_choice('{1} :count documento obrigatório pendente|[2,*] :count documentos obrigatórios pendentes', $nxNPendCiencia, ['count' => $nxNPendCiencia]);
    $nxTemDocsPendentes = $nxNPendCiencia > 0;
    $nxRequerCiencia = ($nxTemDocsPendentes && $processo->status === \App\Enums\ProcessoStatus::EmMontagem) ? '1' : '0';

    $ordemTipoIds = $processo->tipoProcesso?->documentoRegras?->pluck('id')->all() ?? [];
    $nxPosTipo = array_flip($ordemTipoIds);
    $documentosOrdenados = $processo->documentosChecklist->sortBy(fn ($d) => $nxPosTipo[$d->documento_tipo_id] ?? 9999)->values();
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-sm font-medium text-slate-600 dark:text-slate-400">{{ __('Ficha do processo') }}</h2>
            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                <a href="{{ route('processos.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">← {{ __('Processos') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
            @endif

            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:gap-8">
            <div class="min-w-0 flex-1 xl:max-w-4xl">
            <article class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                {{-- Cabeçalho: título, meta, badges, status --}}
                <header class="border-b border-slate-100 px-5 py-5 sm:px-7 sm:py-6 dark:border-slate-800">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1 space-y-4">
                            <h1 class="text-lg font-bold leading-snug text-slate-900 sm:text-xl dark:text-white">
                                {{ $processo->tipoProcesso?->nome ?? __('Processo') }}
                            </h1>
                            <div class="flex flex-wrap gap-x-8 gap-y-2.5 text-sm text-slate-600 dark:text-slate-400">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="h-5 w-5 shrink-0 text-indigo-500 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-9.6-19.5-3.9 19.5" /></svg>
                                    <span class="font-mono tabular-nums"><span class="font-medium text-indigo-600 dark:text-indigo-400">{{ __('Nº') }}</span> <span class="text-slate-800 dark:text-slate-200">{{ $processo->id }}</span></span>
                                </span>
                                <span class="inline-flex items-center gap-2">
                                    <svg class="h-5 w-5 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                    @if ($processo->cliente)
                                        <a href="{{ route('clientes.show', $processo->cliente) }}" class="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">{{ $processo->cliente->nome }}</a>
                                    @else
                                        —
                                    @endif
                                </span>
                                <span class="inline-flex items-center gap-2">
                                    <svg class="h-5 w-5 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                                    <span>{{ $processo->tipoProcesso?->categoria?->label() ?? '—' }}</span>
                                </span>
                                @if (filled($processo->jurisdicao))
                                    <span class="inline-flex min-w-0 items-center gap-2">
                                        <svg class="h-5 w-5 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.125-9 12.375-9 12.375S1.5 17.625 1.5 10.5a9 9 0 1 1 18 0Z" /></svg>
                                        <span class="min-w-0 break-words">{{ $processo->jurisdicao }}</span>
                                    </span>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-900 dark:border-violet-900/50 dark:bg-violet-950/50 dark:text-violet-200">
                                    <svg class="h-4 w-4 text-violet-600 dark:text-violet-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    {{ $processo->status->label() }}
                                </span>
                                @if ($nxTemDocsPendentes)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-orange-200/90 bg-orange-50/90 px-3 py-1 text-xs font-semibold text-orange-900 dark:border-orange-800/80 dark:bg-orange-950/45 dark:text-orange-200">
                                        <x-processo-docs-pendente-icon class="h-4 w-4 text-orange-500 dark:text-orange-400" />
                                        {{ __('Documentos pendentes') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @can('updateStatus', $processo)
                            <div class="shrink-0 lg:pt-0.5">
                                <form
                                    method="POST"
                                    action="{{ route('processos.status', $processo) }}"
                                    class="inline-flex"
                                    data-nx-status-ciencia-form="1"
                                    data-nx-requer-ciencia="{{ $nxRequerCiencia }}"
                                    data-nx-status-submit-on-change="1"
                                    data-nx-status-atual="{{ $processo->status->value }}"
                                    data-nx-swal-titulo="{{ e($nxTituloSwalPendencias) }}"
                                    data-nx-ciencia-linha="{{ e($nxLinhaFicha) }}"
                                    data-nx-ciencia-frase-pendentes="{{ e($nxFraseCiencia) }}"
                                    data-nx-ciencia-texto-secundario="{{ e($nxCienciaTextoSecundario) }}"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="confirmar_ciencia_pendencias_documentais" value="0" autocomplete="off" />
                                    <label class="relative inline-flex min-w-[13.75rem] max-w-[20rem] cursor-pointer items-center gap-2.5 rounded-xl border border-violet-200 bg-violet-50/90 py-2.5 pl-3.5 pr-10 shadow-sm dark:border-violet-900/50 dark:bg-violet-950/40 sm:min-w-[15.25rem] sm:pl-4 sm:pr-11">
                                        <svg class="h-5 w-5 shrink-0 text-violet-600 dark:text-violet-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        <select
                                            name="status"
                                            class="min-w-0 flex-1 cursor-pointer appearance-none border-0 bg-transparent py-0.5 pl-0 pr-1 text-left text-sm font-semibold leading-snug text-violet-950 focus:outline-none focus:ring-0 dark:text-violet-100"
                                            aria-label="{{ __('Alterar etapa do processo') }}"
                                        >
                                            @foreach ($statuses as $st)
                                                <option
                                                    value="{{ $st->value }}"
                                                    style="{{ $st->uiNativeSelectOptionStyle() }}"
                                                    @selected($processo->status === $st)
                                                >{{ $st->label() }}</option>
                                            @endforeach
                                        </select>
                                        <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 shrink-0 -translate-y-1/2 text-violet-600 opacity-80 dark:text-violet-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                    </label>
                                </form>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>
                        @else
                            <div class="shrink-0 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                {{ $processo->status->label() }}
                            </div>
                        @endcan
                    </div>
                </header>

                {{-- Progresso --}}
                <section class="border-b border-slate-100 px-5 py-4 sm:px-7 dark:border-slate-800">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 text-sm font-semibold text-emerald-700 dark:text-emerald-400">
                            <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            {{ __('Progresso') }}
                        </div>
                        <span class="shrink-0 text-sm font-semibold tabular-nums text-slate-600 dark:text-slate-400">
                            {{ $progresso['enviados'] }} / {{ $progresso['total_itens_ativos'] ?? $progresso['obrigatorios_ativos'] }} ({{ $progresso['percentual'] }}%)
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                        {{ __('Considera todos os itens do checklist (incluindo opcionais). Itens dispensados não entram na contagem.') }}
                    </p>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                        <div
                            class="h-full rounded-full transition-all duration-300 {{ ($progresso['percentual'] >= 100 || (($progresso['total_itens_ativos'] ?? $progresso['obrigatorios_ativos'] ?? 0) === 0)) ? 'bg-emerald-500' : 'bg-amber-500' }}"
                            style="width: {{ min(100, (float) $progresso['percentual']) }}%"
                        ></div>
                    </div>
                </section>

                @if ($motivoBloqueio)
                    <div class="mx-5 my-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 sm:mx-7 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-100">
                        <div class="flex gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                            <p>{{ $motivoBloqueio }}</p>
                        </div>
                    </div>
                @endif

                {{-- Checklist (linha compacta como referência visual) --}}
                <section class="pb-2">
                    <h2 class="flex items-center gap-2 border-b border-slate-100 px-5 py-3.5 text-base font-semibold text-indigo-950 sm:px-7 dark:border-slate-800 dark:text-indigo-200">
                        <svg class="h-6 w-6 shrink-0 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.362-.941-2.544-2.25-2.894a48.14 48.14 0 0 0-.75-.06V5.25c0 .621-.504 1.125-1.125 1.125H5.625A1.125 1.125 0 0 1 4.5 5.25v6.108c0 1.362.941 2.544 2.25 2.894.25.082.504.158.75.06M9 12H4.5A2.25 2.25 0 0 1 2.25 9.75v-6A2.25 2.25 0 0 1 4.5 1.125h15A2.25 2.25 0 0 1 21.75 3v6a2.25 2.25 0 0 1-2.25 2.25H18m-10.5-6h7.5m-7.5 6h7.5" /></svg>
                        {{ __('Checklist, orientação CNH/atestado e anexos') }}
                    </h2>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($documentosOrdenados as $doc)
                            @php
                                $slugModelo = (string) ($doc->documentoTipo?->modeloSlugParaRender() ?? '');
                                $codigoTipo = (string) ($doc->documentoTipo?->codigo ?? '');
                                $isResidenciaItem = $codigoTipo === \App\Support\Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP
                                    || $slugModelo === 'anexo-2g';
                                $isAnexo5hItem = \App\Support\Normam211DocumentoCodigos::isDeclaracaoAnexo5h($codigoTipo !== '' ? $codigoTipo : null)
                                    || $slugModelo === 'anexo-5h';
                                $isAnexo5dItem = \App\Support\Normam211DocumentoCodigos::isDeclaracaoAnexo5d($codigoTipo !== '' ? $codigoTipo : null)
                                    || $slugModelo === 'anexo-5d';
                                $isAnexo3dItem = \App\Support\Normam211DocumentoCodigos::isDeclaracaoAnexo3d($codigoTipo !== '' ? $codigoTipo : null)
                                    || $slugModelo === 'anexo-3d-extravio-cha-mta-normam212';
                                $slugRenderLinha = $slugModelo;
                                if ($slugRenderLinha === '') {
                                    if ($codigoTipo === \App\Support\Normam211DocumentoCodigos::COMPROVANTE_RESIDENCIA_CEP) {
                                        $slugRenderLinha = 'anexo-2g';
                                    } elseif (\App\Support\Normam211DocumentoCodigos::isDeclaracaoAnexo5h($codigoTipo)) {
                                        $slugRenderLinha = 'anexo-5h';
                                    } elseif (\App\Support\Normam211DocumentoCodigos::isDeclaracaoAnexo5d($codigoTipo)) {
                                        $slugRenderLinha = 'anexo-5d';
                                    } elseif (\App\Support\Normam211DocumentoCodigos::isDeclaracaoAnexo3d($codigoTipo)) {
                                        $slugRenderLinha = 'anexo-3d-extravio-cha-mta-normam212';
                                    }
                                }
                                $urlModeloRender = null;
                                if ($processo->cliente && $slugRenderLinha !== '') {
                                    $urlModeloRender = route('clientes.documento-modelos.render', [
                                        'cliente' => $processo->cliente,
                                        'slug' => $slugRenderLinha,
                                    ]);
                                    if (\App\Support\ChecklistDocumentoModelo::urlPrecisaContextoEmbarcacao($slugRenderLinha)) {
                                        $embCtx = $processo->embarcacao_id
                                            ? $processo->cliente->embarcacoes->firstWhere('id', (int) $processo->embarcacao_id)
                                            : $processo->cliente->embarcacoes->sortBy('id')->first();
                                        if ($embCtx) {
                                            $urlModeloRender .= (str_contains($urlModeloRender, '?') ? '&' : '?').'contexto_id='.$embCtx->id;
                                        }
                                    }
                                }
                                $st = $doc->status;
                                $urlVisualizarModeloLinha = ($st === \App\Enums\ProcessoDocumentoStatus::Enviado
                                    && $urlModeloRender
                                    && \App\Support\ChecklistDocumentoModelo::satisfeitoViaModeloOuDeclaracaoLegada($doc))
                                    ? $urlModeloRender
                                    : null;
                            @endphp
                            <li class="px-5 py-4 sm:px-7">
                                <div class="flex items-start gap-3 sm:gap-4">
                                    <span class="relative mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center" title="{{ $st->label() }}">
                                        @if ($st === \App\Enums\ProcessoDocumentoStatus::Enviado || $st === \App\Enums\ProcessoDocumentoStatus::Fisico)
                                            <svg class="h-7 w-7 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        @elseif ($st === \App\Enums\ProcessoDocumentoStatus::Dispensado)
                                            <svg class="h-7 w-7 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                                        @else
                                            <svg class="h-7 w-7 text-amber-500 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        @endif
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="py-2 text-sm font-medium leading-snug text-slate-800 dark:text-slate-200">
                                            {{ $doc->documentoTipo->nome }}
                                        </p>
                                        @if (\App\Support\ChaChecklistDocumentoCodigos::isCnhComValidade($codigoTipo))
                                            @can('updateDocumento', $processo)
                                                <form method="POST" action="{{ route('processos.documentos.update', [$processo, $doc]) }}" class="mt-2 flex flex-wrap items-center gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ $st->value }}" />
                                                    <label class="text-xs font-medium text-slate-600 dark:text-slate-400" for="nx-validade-cnh-{{ $doc->id }}">{{ __('Validade da CNH') }}</label>
                                                    <input
                                                        type="text"
                                                        id="nx-validade-cnh-{{ $doc->id }}"
                                                        name="data_validade_documento"
                                                        value="{{ $doc->data_validade_documento?->format('d/m/Y') }}"
                                                        inputmode="numeric"
                                                        maxlength="10"
                                                        autocomplete="off"
                                                        placeholder="dd/mm/aaaa"
                                                        data-nx-mask="date-br"
                                                        class="rounded-md border border-slate-300 bg-white px-2 py-1 text-xs text-slate-800 shadow-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                                                    />
                                                    <button type="submit" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                                                        {{ __('Guardar validade') }}
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                        @if (\App\Support\ChaChecklistDocumentoCodigos::isAtestadoMedicoPsicofisico($codigoTipo) && $st === \App\Enums\ProcessoDocumentoStatus::Dispensado)
                                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Dispensado automaticamente: CNH válida anexada com data de validade em vigor.') }}</p>
                                        @endif
                                    </div>
                                    <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                                        @if ($st === \App\Enums\ProcessoDocumentoStatus::Pendente)
                                            @can('updateDocumento', $processo)
                                                @if ($urlModeloRender && $doc->preenchido_via_modelo)
                                                    @php
                                                        $urlModeloPrint = $urlModeloRender.(str_contains($urlModeloRender, '?') ? '&' : '?').'print=1';
                                                        $urlModeloPdf = $urlModeloRender.(str_contains($urlModeloRender, '?') ? '&' : '?').'format=pdf';
                                                        $urlModeloDoc = $urlModeloRender.(str_contains($urlModeloRender, '?') ? '&' : '?').'format=doc';
                                                        $nxBaixarId = 'nx-baixar-modelo-'.$doc->id;
                                                    @endphp
                                                    <a
                                                        href="{{ $urlModeloRender }}"
                                                        target="_blank"
                                                        rel="noopener"
                                                        class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-900 shadow-sm hover:bg-indigo-100 dark:border-indigo-900/40 dark:bg-indigo-950/50 dark:text-indigo-200"
                                                    >
                                                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                        {{ __('Abrir modelo') }}
                                                    </a>
                                                    <label class="relative inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                                                        <svg class="h-3.5 w-3.5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                                        <span>{{ __('Baixar') }}</span>
                                                        <select
                                                            id="{{ $nxBaixarId }}"
                                                            aria-label="{{ __('Baixar') }}"
                                                            class="absolute inset-0 h-full w-full cursor-pointer appearance-none opacity-0"
                                                            onchange="const v=this.value; if(v){ window.open(v,'_blank','noopener'); } this.selectedIndex=0;"
                                                        >
                                                            <option value="" selected>{{ __('Baixar') }}</option>
                                                            <option value="{{ $urlModeloPdf }}">{{ __('PDF') }}</option>
                                                            <option value="{{ $urlModeloDoc }}">{{ __('DOC') }}</option>
                                                            <option value="{{ $urlModeloPrint }}">{{ __('Imprimir') }}</option>
                                                        </select>
                                                        <svg class="pointer-events-none absolute right-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500 dark:text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                                    </label>
                                                @endif
                                                @if (\App\Support\ChecklistDocumentoModelo::tipoTemModelo($doc->documentoTipo) && ! $isResidenciaItem && ! $isAnexo5hItem && ! $isAnexo5dItem && ! $isAnexo3dItem)
                                                    <form method="POST" action="{{ route('processos.documentos.update', [$processo, $doc]) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ \App\Enums\ProcessoDocumentoStatus::Enviado->value }}" />
                                                        <input type="hidden" name="preenchido_via_modelo" value="1" />
                                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-900 shadow-sm hover:bg-teal-100 dark:border-teal-900/40 dark:bg-teal-950/50 dark:text-teal-200">
                                                            {{ __('Gerar') }}
                                                        </button>
                                                    </form>
                                                @endif
                                                @if ($isResidenciaItem)
                                                    <form method="POST" action="{{ route('processos.documentos.update', [$processo, $doc]) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ \App\Enums\ProcessoDocumentoStatus::Enviado->value }}" />
                                                        <input type="hidden" name="declaracao_residencia_2g" value="1" />
                                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-900 shadow-sm hover:bg-sky-100 dark:border-sky-900/40 dark:bg-sky-950/50 dark:text-sky-200">
                                                            {{ __('Declaração') }}
                                                        </button>
                                                    </form>
                                                @endif
                                                @if ($isAnexo5hItem)
                                                    <form method="POST" action="{{ route('processos.documentos.update', [$processo, $doc]) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ \App\Enums\ProcessoDocumentoStatus::Enviado->value }}" />
                                                        <input type="hidden" name="declaracao_anexo_5h" value="1" />
                                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-violet-200 bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-900 shadow-sm hover:bg-violet-100 dark:border-violet-900/40 dark:bg-violet-950/50 dark:text-violet-200">
                                                            {{ __('Requerimento') }}
                                                        </button>
                                                    </form>
                                                @endif
                                                @if ($isAnexo5dItem)
                                                    <form method="POST" action="{{ route('processos.documentos.update', [$processo, $doc]) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ \App\Enums\ProcessoDocumentoStatus::Enviado->value }}" />
                                                        <input type="hidden" name="declaracao_anexo_5d" value="1" />
                                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-violet-200 bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-900 shadow-sm hover:bg-violet-100 dark:border-violet-900/40 dark:bg-violet-950/50 dark:text-violet-200">
                                                            {{ __('Declaração') }}
                                                        </button>
                                                    </form>
                                                @endif
                                                @if ($isAnexo3dItem)
                                                    <form method="POST" action="{{ route('processos.documentos.update', [$processo, $doc]) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ \App\Enums\ProcessoDocumentoStatus::Enviado->value }}" />
                                                        <input type="hidden" name="declaracao_anexo_3d" value="1" />
                                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-violet-200 bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-900 shadow-sm hover:bg-violet-100 dark:border-violet-900/40 dark:bg-violet-950/50 dark:text-violet-200">
                                                            {{ __('Declaração') }} <span class="font-normal opacity-80">(NORMAM 212)</span>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('processos.documentos.anexos.store', [$processo, $doc]) }}" enctype="multipart/form-data" class="inline">
                                                    @csrf
                                                    <input type="file" name="arquivos[]" multiple class="hidden" accept="{{ \App\Support\ChecklistDocumentoMultiplosAnexos::permite($codigoTipo) ? '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp' : '.pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,application/pdf,image/*' }}" id="nx-ficha-file-{{ $doc->id }}" onchange="this.form.requestSubmit()" />
                                                    <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" onclick="document.getElementById('nx-ficha-file-{{ $doc->id }}').click()">
                                                        <svg class="h-3.5 w-3.5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m0 0 .01.01m7.364-7.364L12 10.5" /></svg>
                                                        {{ __('Anexar') }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('processos.documentos.update', [$processo, $doc]) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ \App\Enums\ProcessoDocumentoStatus::Fisico->value }}" />
                                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-900 shadow-sm hover:bg-indigo-100 dark:border-indigo-900/40 dark:bg-indigo-950/50 dark:text-indigo-200">
                                                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                                        {{ __('Físico') }}
                                                    </button>
                                                </form>
                                            @endcan
                                        @elseif ($st === \App\Enums\ProcessoDocumentoStatus::Enviado && $doc->anexos->isNotEmpty())
                                            @if (\App\Support\ChecklistDocumentoMultiplosAnexos::permite($codigoTipo))
                                                <div class="flex max-w-md flex-col items-end gap-2">
                                                    @foreach ($doc->anexos as $anexo)
                                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                                            <a
                                                                href="{{ $anexo->urlPublica() }}"
                                                                target="_blank"
                                                                rel="noopener"
                                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                                                            >
                                                                <svg class="h-3.5 w-3.5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                                {{ \Illuminate\Support\Str::limit($anexo->nome_original, 36) }}
                                                            </a>
                                                            @can('updateDocumento', $processo)
                                                                <form
                                                                    method="POST"
                                                                    action="{{ route('processos.documentos.anexos.destroy', [$processo, $doc, $anexo]) }}"
                                                                    class="inline"
                                                                    onsubmit="return confirm(@json(__('Remover este anexo?')))"
                                                                >
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-900 shadow-sm hover:bg-red-100 dark:border-red-900/40 dark:bg-red-950/40 dark:text-red-200">
                                                                        {{ __('Remover') }}
                                                                    </button>
                                                                </form>
                                                            @endcan
                                                        </div>
                                                    @endforeach
                                                    @can('updateDocumento', $processo)
                                                        <p class="text-right text-[11px] leading-snug text-slate-500 dark:text-slate-400">{{ __('Pode anexar várias fotos (JPG, PNG ou WebP).') }}</p>
                                                        <form method="POST" action="{{ route('processos.documentos.anexos.store', [$processo, $doc]) }}" enctype="multipart/form-data" class="inline">
                                                            @csrf
                                                            <input type="file" name="arquivos[]" multiple class="hidden" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" id="nx-ficha-file-mais-{{ $doc->id }}" onchange="this.form.requestSubmit()" />
                                                            <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" onclick="document.getElementById('nx-ficha-file-mais-{{ $doc->id }}').click()">
                                                                <svg class="h-3.5 w-3.5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m0 0 .01.01m7.364-7.364L12 10.5" /></svg>
                                                                {{ __('Anexar mais') }}
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            @else
                                            <a
                                                href="{{ $doc->anexos->first()->urlPublica() }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                                            >
                                                <svg class="h-3.5 w-3.5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                {{ __('Visualizar') }}
                                            </a>
                                            @endif
                                        @elseif ($urlVisualizarModeloLinha)
                                            @php
                                                $urlModeloPrint = $urlVisualizarModeloLinha.(str_contains($urlVisualizarModeloLinha, '?') ? '&' : '?').'print=1';
                                                $urlModeloPdf = $urlVisualizarModeloLinha.(str_contains($urlVisualizarModeloLinha, '?') ? '&' : '?').'format=pdf';
                                                $urlModeloDoc = $urlVisualizarModeloLinha.(str_contains($urlVisualizarModeloLinha, '?') ? '&' : '?').'format=doc';
                                                $nxBaixarId = 'nx-baixar-modelo-v-'.$doc->id;
                                            @endphp
                                            <a
                                                href="{{ $urlVisualizarModeloLinha }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-900 shadow-sm hover:bg-indigo-100 dark:border-indigo-900/40 dark:bg-indigo-950/50 dark:text-indigo-200"
                                            >
                                                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                {{ __('Abrir modelo') }}
                                            </a>
                                            <label class="relative inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                                                <svg class="h-3.5 w-3.5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                                <span>{{ __('Baixar') }}</span>
                                                <select
                                                    id="{{ $nxBaixarId }}"
                                                    aria-label="{{ __('Baixar') }}"
                                                    class="absolute inset-0 h-full w-full cursor-pointer appearance-none opacity-0"
                                                    onchange="const v=this.value; if(v){ window.open(v,'_blank','noopener'); } this.selectedIndex=0;"
                                                >
                                                    <option value="" selected>{{ __('Baixar') }}</option>
                                                    <option value="{{ $urlModeloPdf }}">{{ __('PDF') }}</option>
                                                    <option value="{{ $urlModeloDoc }}">{{ __('DOC') }}</option>
                                                    <option value="{{ $urlModeloPrint }}">{{ __('Imprimir') }}</option>
                                                </select>
                                                <svg class="pointer-events-none absolute right-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500 dark:text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                                            </label>
                                        @elseif ($st === \App\Enums\ProcessoDocumentoStatus::Dispensado && ! $isResidenciaItem && ! $isAnexo5hItem && ! $isAnexo5dItem && ! $isAnexo3dItem)
                                            @can('updateDocumento', $processo)
                                                <form method="POST" action="{{ route('processos.documentos.anexos.store', [$processo, $doc]) }}" enctype="multipart/form-data" class="inline">
                                                    @csrf
                                                    <input type="file" name="arquivos[]" multiple class="hidden" accept="{{ \App\Support\ChecklistDocumentoMultiplosAnexos::permite($codigoTipo) ? '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp' : '.pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,application/pdf,image/*' }}" id="nx-ficha-file-{{ $doc->id }}" onchange="this.form.requestSubmit()" />
                                                    <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" onclick="document.getElementById('nx-ficha-file-{{ $doc->id }}').click()">
                                                        <svg class="h-3.5 w-3.5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m0 0 .01.01m7.364-7.364L12 10.5" /></svg>
                                                        {{ __('Anexar') }}
                                                    </button>
                                                </form>
                                            @endcan
                                        @elseif ($st === \App\Enums\ProcessoDocumentoStatus::Fisico)
                                            @can('updateDocumento', $processo)
                                                <form
                                                    method="POST"
                                                    action="{{ route('processos.documentos.update', [$processo, $doc]) }}"
                                                    class="inline"
                                                    data-nx-trocar-anexo-form="1"
                                                    data-nx-trocar-anexo-titulo="{{ e(__('Trocar anexo?')) }}"
                                                    data-nx-trocar-anexo-linha="{{ e($nxLinhaFicha) }}"
                                                    data-nx-trocar-anexo-frase="{{ e(__('O ficheiro enviado anteriormente será eliminado ao substituir por um novo anexo.')) }}"
                                                    data-nx-trocar-anexo-pergunta="{{ e(__('Deseja realmente trocar o anexo?')) }}"
                                                    data-nx-trocar-anexo-confirmar="{{ e(__('Sim, trocar')) }}"
                                                    data-nx-trocar-anexo-cancelar="{{ e(__('Não, cancelar')) }}"
                                                >
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ \App\Enums\ProcessoDocumentoStatus::Pendente->value }}" />
                                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">
                                                        {{ __('Trocar anexo') }}
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @if ($errors->has('arquivos') || $errors->has('arquivos.*'))
                        <div class="border-t border-slate-100 px-5 py-3 sm:px-7 dark:border-slate-800">
                            <x-input-error :messages="$errors->get('arquivos')" />
                            <x-input-error :messages="$errors->get('arquivos.*')" />
                        </div>
                    @endif
                </section>
            </article>
            </div>

            <aside
                class="w-full shrink-0 xl:sticky xl:top-6 xl:w-80"
                x-data="nxProcessoPostIts(@js($nxPostItsCfg))"
            >
                <div class="relative rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50 via-yellow-50 to-amber-100/90 p-4 shadow-md ring-1 ring-amber-900/5 dark:border-amber-900/40 dark:from-amber-950/40 dark:via-amber-950/30 dark:to-amber-950/50 dark:ring-amber-100/10">
                    <div class="absolute -right-1 -top-1 flex h-8 w-8 items-center justify-center rounded-full bg-amber-200/90 text-amber-900 shadow-sm dark:bg-amber-800/80 dark:text-amber-100" aria-hidden="true">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 18H15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 15 4.5h-4.5A2.25 2.25 0 0 0 8.25 6.75v12A2.25 2.25 0 0 0 10.5 21Z" /></svg>
                    </div>
                    <h2 class="pr-8 text-sm font-bold uppercase tracking-wide text-amber-950 dark:text-amber-100">{{ __('Notas rápidas') }}</h2>
                    <p class="mt-1 text-xs text-amber-900/70 dark:text-amber-200/70">{{ __('Observações do processo.') }}</p>

                    @can('updateDocumento', $processo)
                        <div class="mt-4 space-y-2">
                            <label class="sr-only" for="nx_post_it_novo">{{ __('Nova nota') }}</label>
                            <textarea
                                id="nx_post_it_novo"
                                x-model="draft"
                                rows="3"
                                maxlength="5000"
                                class="w-full resize-y rounded-lg border border-amber-300/80 bg-white/90 px-3 py-2 text-sm text-amber-950 shadow-inner placeholder:text-amber-800/40 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-400/30 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-50 dark:placeholder:text-amber-200/30"
                                placeholder="{{ __('Escreva uma nota…') }}"
                                @keydown.ctrl.enter.prevent="add()"
                                @keydown.meta.enter.prevent="add()"
                            ></textarea>
                            <div class="flex flex-wrap items-center gap-2">
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-amber-500 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-amber-700 dark:hover:bg-amber-600"
                                    @click="add()"
                                    :disabled="busy || !draft.trim()"
                                >
                                    {{ __('Adicionar') }}
                                </button>
                                <span x-show="busy" x-cloak class="text-xs text-amber-800/80 dark:text-amber-200/80">{{ __('A guardar…') }}</span>
                            </div>
                        </div>
                    @endcan

                    <p x-show="error" x-cloak x-text="error" class="mt-3 text-xs font-medium text-red-700 dark:text-red-300"></p>

                    <ul class="mt-4 space-y-3">
                        <li
                            x-show="items.length === 0"
                            x-cloak
                            class="rounded-lg border border-dashed border-amber-300/60 bg-white/40 px-3 py-4 text-center text-xs text-amber-900/60 dark:border-amber-800/50 dark:bg-amber-950/20 dark:text-amber-200/50"
                        >
                            {{ __('Nenhuma nota ainda.') }}
                        </li>
                        <template x-for="it in items" :key="it.id">
                            <li class="relative rotate-[0.15deg] rounded-lg border border-amber-300/70 bg-[#fffbeb] px-3 py-3 text-sm text-amber-950 shadow-sm dark:border-amber-800/50 dark:bg-amber-950/35 dark:text-amber-50">
                                <template x-if="editingId !== it.id">
                                    <div>
                                        <p class="whitespace-pre-wrap" x-text="it.conteudo"></p>
                                        <div class="mt-2 flex flex-wrap items-center justify-between gap-2 border-t border-amber-200/60 pt-2 text-[10px] text-amber-800/70 dark:border-amber-800/40 dark:text-amber-200/60">
                                            <span x-show="it.user && it.user.name" x-text="it.user.name"></span>
                                            <span class="tabular-nums" x-text="it.updated_at ? new Date(it.updated_at).toLocaleString() : ''"></span>
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-2" x-show="canEdit" x-cloak>
                                            <button type="button" class="text-xs font-semibold text-amber-800 underline decoration-amber-400/80 hover:text-amber-950 dark:text-amber-200 dark:hover:text-white" @click="startEdit(it)">{{ __('Editar') }}</button>
                                            <button type="button" class="text-xs font-semibold text-red-700 underline decoration-red-300 hover:text-red-900 dark:text-red-300 dark:hover:text-red-200" @click="remove(it.id)">{{ __('Excluir') }}</button>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="editingId === it.id">
                                    <div class="space-y-2">
                                        <label class="sr-only" :for="'nx_post_it_edit_' + it.id">{{ __('Editar nota') }}</label>
                                        <textarea
                                            :id="'nx_post_it_edit_' + it.id"
                                            x-model="editDraft"
                                            rows="4"
                                            maxlength="5000"
                                            class="w-full resize-y rounded border border-amber-400/80 bg-white px-2 py-1.5 text-sm focus:border-amber-600 focus:outline-none focus:ring-1 focus:ring-amber-500/30 dark:border-amber-700 dark:bg-amber-950/50 dark:text-amber-50"
                                        ></textarea>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" class="rounded-lg bg-amber-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-amber-500 disabled:opacity-50 dark:bg-amber-700" @click="saveEdit()" :disabled="busy || !editDraft.trim()">{{ __('Guardar') }}</button>
                                            <button type="button" class="rounded-lg border border-amber-400/80 px-2.5 py-1.5 text-xs font-semibold text-amber-900 hover:bg-amber-100/80 dark:border-amber-700 dark:text-amber-100 dark:hover:bg-amber-900/40" @click="cancelEdit()">{{ __('Cancelar') }}</button>
                                        </div>
                                    </div>
                                </template>
                            </li>
                        </template>
                    </ul>
                </div>
            </aside>
            </div>
        </div>
    </div>
</x-app-layout>
