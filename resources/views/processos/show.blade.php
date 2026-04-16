@inject('nxStatusSvc', \App\Services\ProcessoStatusService::class)
@php
    use App\Enums\ProcessoStatus;

    $nxLinhaFicha = ($processo->tipoProcesso?->nome ?? __('Processo'));
    $nxTemDocsPendentes = $nxStatusSvc->quantidadeDocumentosObrigatoriosPendentes($processo) > 0;
    $nxTituloSwalPendencias = __('Processo com pendências');
    $nxCienciaTextoSecundario = __('Deseja realmente alterar o status mesmo assim?');
    $nxNPendCienciaFicha = $nxStatusSvc->quantidadeDocumentosObrigatoriosPendentes($processo);
    $nxFraseCienciaFicha = trans_choice('{1} :count documento obrigatório pendente|[2,*] :count documentos obrigatórios pendentes', $nxNPendCienciaFicha, ['count' => $nxNPendCienciaFicha]);
    $nxRequerCienciaFicha = ($nxNPendCienciaFicha > 0 && $processo->status === ProcessoStatus::EmMontagem) ? '1' : '0';
    $nxLinhaCienciaFicha = ($processo->tipoProcesso?->nome ?? __('Processo')).' — '.($processo->cliente?->nome ?? __('Sem cliente'));

    $ordemTipoIds = $processo->tipoProcesso?->documentoRegras?->pluck('id')->all() ?? [];
    $nxPosTipo = array_flip($ordemTipoIds);
    $documentosOrdenados = $processo->documentosChecklist->sortBy(fn ($d) => $nxPosTipo[$d->documento_tipo_id] ?? 9999)->values();

    /** PDF/DOC/imprimir a partir do checklist (desativado por pedido; voltar a `true` para mostrar). */
    $nxFichaChecklistMostrarBaixarModelo = false;
    /** Voltar modelo gerado a «Pendente» (desativado por pedido). */
    $nxFichaChecklistMostrarAnularCorrigirModelo = false;
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
            <article
                class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900"
                x-data="{
                    faltaProtocolo: {{ $processo->faltaIdentificacaoProtocoloMarinha() ? 'true' : 'false' }},
                    protoBusy: false,
                    protoOk: null,
                    protoErrNumero: @js($errors->first('marinha_protocolo_numero')),
                    protoErrData: @js($errors->first('marinha_protocolo_data')),
                    protoErrAnexo: @js($errors->first('marinha_protocolo_anexo')),
                    protoAnexoNome: @js($processo->marinha_protocolo_anexo_original_name),
                    protoAnexoUrl: @js(filled($processo->marinha_protocolo_anexo_path) ? route('processos.protocolo-marinha.anexo', $processo) : null),
                    protoAnexoLocalPreviewUrl: null,
                    protoAnexoLocalNome: null,
                    protoAnexoLocalIsImage: false,
                    protoAnexoRevokeLocalPreview() {
                        if (this.protoAnexoLocalPreviewUrl) {
                            URL.revokeObjectURL(this.protoAnexoLocalPreviewUrl);
                            this.protoAnexoLocalPreviewUrl = null;
                        }
                    },
                    protoAnexoNomeIsImage(nm) {
                        if (typeof nm !== 'string' || nm === '') {
                            return false;
                        }

                        return /\\.(jpe?g|png|gif|webp)$/i.test(nm);
                    },
                    protoAnexoSyncLocalPreview() {
                        this.protoAnexoRevokeLocalPreview();
                        const inp = this.$refs.marinhaProtocoloAnexoInput;
                        if (! inp || ! inp.files || ! inp.files.length) {
                            this.protoAnexoLocalNome = null;
                            this.protoAnexoLocalIsImage = false;
                            return;
                        }
                        const f = inp.files[0];
                        this.protoAnexoLocalNome = f.name;
                        this.protoAnexoLocalIsImage = /^image\\//.test(f.type || '') || this.protoAnexoNomeIsImage(f.name || '');
                        if (this.protoAnexoLocalIsImage) {
                            this.protoAnexoLocalPreviewUrl = URL.createObjectURL(f);
                        }
                    },
                    async saveProtocoloMarinha(ev) {
                        ev.preventDefault();
                        const form = ev.target;
                        this.protoOk = null;
                        this.protoErrNumero = null;
                        this.protoErrData = null;
                        this.protoErrAnexo = null;
                        this.protoBusy = true;
                        const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                        const fd = new FormData(form);
                        let res;
                        try {
                            res = await fetch(form.action, {
                                method: 'POST',
                                body: fd,
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': token,
                                },
                                credentials: 'same-origin',
                            });
                        } catch (_) {
                            this.protoBusy = false;
                            this.protoErrNumero = @json(__('Ligação interrompida. Tente de novo.'));
                            return;
                        }
                        let data = {};
                        try {
                            data = await res.json();
                        } catch (_) {}
                        this.protoBusy = false;
                        if (! res.ok) {
                            const errs = data.errors || {};
                            this.protoErrNumero = errs.marinha_protocolo_numero ? errs.marinha_protocolo_numero[0] : null;
                            this.protoErrData = errs.marinha_protocolo_data ? errs.marinha_protocolo_data[0] : null;
                            this.protoErrAnexo = errs.marinha_protocolo_anexo ? errs.marinha_protocolo_anexo[0] : null;
                            if (! this.protoErrNumero && ! this.protoErrData && ! this.protoErrAnexo && data.message) {
                                this.protoErrNumero = data.message;
                            }
                            return;
                        }
                        this.protoOk = data.message || '';
                        this.faltaProtocolo = typeof data.falta_protocolo === 'boolean' ? data.falta_protocolo : false;
                        if (Object.prototype.hasOwnProperty.call(data, 'marinha_protocolo_anexo_original_name')) {
                            this.protoAnexoNome = data.marinha_protocolo_anexo_original_name || null;
                        }
                        if (Object.prototype.hasOwnProperty.call(data, 'marinha_protocolo_anexo_url')) {
                            this.protoAnexoUrl = data.marinha_protocolo_anexo_url || null;
                        }
                        const fileInp = form.querySelector('input[name=marinha_protocolo_anexo]');
                        if (fileInp) {
                            fileInp.value = '';
                        }
                        this.protoAnexoRevokeLocalPreview();
                        this.protoAnexoLocalNome = null;
                        this.protoAnexoLocalIsImage = false;
                        const rem = form.querySelector('input[name=remover_marinha_protocolo_anexo]');
                        if (rem && rem.type === 'checkbox') {
                            rem.checked = false;
                        }
                    },
                }"
            >
                {{-- Cabeçalho: título, meta, badges, status --}}
                <header class="border-b border-slate-100 px-5 py-5 sm:px-7 sm:py-6 dark:border-slate-800">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1 space-y-4">
                            <div class="flex flex-wrap items-center gap-2 gap-y-1">
                                <h1 class="min-w-0 text-lg font-bold leading-snug text-slate-900 sm:text-xl dark:text-white">
                                    {{ $processo->tipoProcesso?->nome ?? __('Processo') }}
                                </h1>
                                @if ($processo->status->exigeDadosProtocoloMarinha())
                                    <span
                                        x-show="faltaProtocolo"
                                        x-cloak
                                        class="inline-flex shrink-0 text-orange-500 dark:text-orange-400"
                                        title="{{ __('Falta indicar o número de protocolo da Marinha. Utilize a secção «Protocolo da Marinha» abaixo.') }}"
                                        aria-label="{{ __('Falta indicar o número de protocolo da Marinha. Utilize a secção «Protocolo da Marinha» abaixo.') }}"
                                    >
                                        <x-processo-protocolo-marinha-alerta-icon class="h-5 w-5" />
                                    </span>
                                @endif
                            </div>
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
                            @php $nxStFicha = $processo->status; @endphp
                            <div class="flex min-w-0 shrink-0 flex-col items-stretch gap-2 sm:max-w-[20rem] sm:items-end lg:pt-0.5">
                                <form
                                    method="POST"
                                    action="{{ route('processos.status', $processo) }}"
                                    class="w-full min-w-[13.75rem] sm:min-w-[15.25rem]"
                                    data-nx-status-ciencia-form="1"
                                    data-nx-requer-ciencia="{{ $nxRequerCienciaFicha }}"
                                    data-nx-status-submit-on-change="1"
                                    data-nx-status-atual="{{ $processo->status->value }}"
                                    data-nx-swal-titulo="{{ e($nxTituloSwalPendencias) }}"
                                    data-nx-ciencia-linha="{{ e($nxLinhaCienciaFicha) }}"
                                    data-nx-ciencia-frase-pendentes="{{ e($nxFraseCienciaFicha) }}"
                                    data-nx-ciencia-texto-secundario="{{ e($nxCienciaTextoSecundario) }}"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="confirmar_ciencia_pendencias_documentais" value="0" autocomplete="off" />
                                    <div class="block w-full">
                                        <span class="mb-1.5 flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400">
                                            <svg class="h-4 w-4 shrink-0 text-violet-600 dark:text-violet-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                            {{ __('Alterar etapa') }}
                                        </span>
                                        <x-processo-status-custom-select :processo="$processo" :chrome-wrap-class="$nxStFicha->uiListSelectChromeWrapClass()" />
                                    </div>
                                </form>
                                <a
                                    href="{{ route('processos.kanban') }}"
                                    class="text-center text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 sm:text-right"
                                >
                                    {{ __('Ou no Kanban (arrastar o card) →') }}
                                </a>
                                <x-input-error :messages="$errors->get('status')" class="mt-0 sm:text-right" />
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

                @if ($processo->status === ProcessoStatus::AguardandoProva)
                    <section class="border-b border-sky-200/80 bg-gradient-to-br from-sky-50/90 via-white to-sky-100/40 px-5 py-5 sm:px-7 dark:border-sky-900/40 dark:from-sky-950/35 dark:via-slate-900 dark:to-sky-950/20">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 class="text-base font-semibold text-sky-950 dark:text-sky-100">{{ __('Prova na Marinha') }}</h2>
                                <p class="mt-1 text-xs text-sky-900/85 dark:text-sky-200/85">{{ __('Indique a data em que a prova prática está marcada no órgão. Esta data aparece no dashboard da empresa.') }}</p>
                            </div>
                        </div>
                        @can('updateDocumento', $processo)
                            <form method="POST" action="{{ route('processos.prova-marinha.update', $processo) }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                                @csrf
                                @method('PATCH')
                                <div class="min-w-0 flex-1 sm:max-w-xs">
                                    <x-input-label for="marinha_prova_data" :value="__('Data da prova')" class="!mb-1" />
                                    <x-text-input
                                        id="marinha_prova_data"
                                        name="marinha_prova_data"
                                        type="date"
                                        class="mt-0 block w-full"
                                        :value="old('marinha_prova_data', $processo->marinha_prova_data?->format('Y-m-d'))"
                                    />
                                    <x-input-error :messages="$errors->get('marinha_prova_data')" class="mt-1" />
                                </div>
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                                >
                                    {{ __('Guardar data') }}
                                </button>
                            </form>
                        @else
                            <p class="mt-3 text-sm font-medium text-slate-800 dark:text-slate-200">
                                {{ $processo->marinha_prova_data?->format('d/m/Y') ?? '—' }}
                            </p>
                        @endcan
                    </section>
                @endif

                @if ($processo->status->exigeDadosProtocoloMarinha())
                    <section class="border-b border-violet-200/90 bg-gradient-to-br from-violet-50/95 via-white to-violet-100/50 px-5 py-5 sm:px-7 dark:border-violet-800/45 dark:from-violet-950/40 dark:via-slate-900 dark:to-violet-950/25">
                        <div class="mb-4 flex gap-3 rounded-xl border border-amber-200 bg-amber-50/90 px-4 py-3 text-sm text-amber-950 dark:border-amber-900/50 dark:bg-amber-950/35 dark:text-amber-100" x-show="faltaProtocolo" x-cloak>
                            <x-processo-protocolo-marinha-alerta-icon class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" />
                            <p>{{ __('Registe o número e a data de protocolo da Marinha para esta etapa.') }}</p>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 class="text-base font-semibold text-violet-950 dark:text-violet-100">{{ __('Protocolo da Marinha') }}</h2>
                                <p class="mt-1 text-xs text-violet-900/80 dark:text-violet-200/90">{{ __('Número e data da protocolação entregues pelo órgão. Pode anexar o comprovativo (PDF ou imagem), opcionalmente. Para gravar na ficha, use «Guardar número e data» (também envia o anexo se tiver escolhido um ficheiro).') }}</p>
                            </div>
                        </div>
                        @can('updateDocumento', $processo)
                            <form method="POST" action="{{ route('processos.protocolo-marinha.update', $processo) }}" class="mt-4" @submit="saveProtocoloMarinha($event)">
                                @csrf
                                @method('PATCH')
                                <input
                                    id="marinha_protocolo_anexo"
                                    x-ref="marinhaProtocoloAnexoInput"
                                    type="file"
                                    name="marinha_protocolo_anexo"
                                    accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
                                    class="hidden"
                                    @change="protoAnexoSyncLocalPreview()"
                                />
                                <div class="flex flex-col gap-3 sm:flex-row sm:flex-nowrap sm:items-end">
                                    <div class="min-w-0 flex-1">
                                        <x-input-label for="marinha_protocolo_numero" :value="__('Número de protocolo')" class="!mb-1" />
                                        <x-text-input
                                            id="marinha_protocolo_numero"
                                            name="marinha_protocolo_numero"
                                            type="text"
                                            class="mt-0 block w-full"
                                            :value="old('marinha_protocolo_numero', $processo->marinha_protocolo_numero)"
                                            autocomplete="off"
                                        />
                                    </div>
                                    <div class="w-full shrink-0 sm:w-40">
                                        <x-input-label for="marinha_protocolo_data" :value="__('Data da protocolação')" class="!mb-1" />
                                        <x-text-input
                                            id="marinha_protocolo_data"
                                            name="marinha_protocolo_data"
                                            type="date"
                                            class="mt-0 block w-full"
                                            :value="old('marinha_protocolo_data', $processo->marinha_protocolo_data?->format('Y-m-d'))"
                                        />
                                    </div>
                                    <div class="flex w-full shrink-0 flex-wrap items-center justify-center gap-2 sm:w-auto sm:justify-start sm:pb-0.5">
                                        <button
                                            type="button"
                                            class="inline-flex size-[2.75rem] shrink-0 items-center justify-center rounded-md border border-slate-300 bg-white text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                                            title="{{ __('Anexar') }}"
                                            aria-label="{{ __('Anexar') }}"
                                            @click="$refs.marinhaProtocoloAnexoInput.click()"
                                        >
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.009-.01-.01m7.364-7.364L12 10.5" /></svg>
                                        </button>
                                        <a
                                            href="#"
                                            role="button"
                                            tabindex="0"
                                            class="nx-processo-protocolo-submit inline-flex size-[2.75rem] shrink-0 cursor-pointer items-center justify-center rounded-lg bg-violet-600 !text-white no-underline shadow-sm hover:bg-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 focus:ring-offset-white dark:bg-violet-600 dark:hover:bg-violet-500 dark:focus:ring-offset-slate-900"
                                            x-bind:class="{ 'pointer-events-none cursor-not-allowed opacity-60': protoBusy }"
                                            x-bind:aria-busy="protoBusy"
                                            x-bind:aria-disabled="protoBusy"
                                            x-bind:aria-label="protoBusy ? @js(__('A guardar na ficha…')) : @js(__('Guardar número, data e anexo do protocolo da Marinha na ficha'))"
                                            x-bind:title="protoBusy ? @js(__('A guardar na ficha…')) : @js(__('Guardar número e data'))"
                                            @click.prevent="if (!protoBusy) $el.closest('form').requestSubmit()"
                                            @keydown.enter.prevent="if (!protoBusy) $el.closest('form').requestSubmit()"
                                            @keydown.space.prevent="if (!protoBusy) $el.closest('form').requestSubmit()"
                                        >
                                            <span class="sr-only">{{ __('Guardar número e data') }}</span>
                                            {{-- Disquete «guardar» (traço branco via currentColor + nx-processo-protocolo-submit) --}}
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-6 w-6 shrink-0"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="1.75"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true"
                                                focusable="false"
                                                x-show="!protoBusy"
                                            >
                                                <path d="M6 4h10l4 4v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z" />
                                                <path d="M14 4v4H8V4" />
                                                <path d="M8.25 12h7.5v6.25H8.25z" />
                                            </svg>
                                            <svg
                                                class="h-5 w-5 shrink-0 animate-spin"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                aria-hidden="true"
                                                x-cloak
                                                x-show="protoBusy"
                                            >
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="mt-4 w-full max-w-2xl space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <img
                                            x-show="protoAnexoLocalIsImage && protoAnexoLocalPreviewUrl"
                                            x-cloak
                                            :src="protoAnexoLocalPreviewUrl"
                                            alt=""
                                            class="h-9 w-9 shrink-0 rounded-md object-cover ring-1 ring-slate-200 dark:ring-slate-600"
                                        />
                                        <span
                                            x-show="protoAnexoLocalNome"
                                            x-cloak
                                            class="max-w-[min(100%,14rem)] truncate text-sm font-medium text-slate-800 dark:text-slate-200"
                                            x-text="protoAnexoLocalNome"
                                        ></span>
                                        <span x-show="protoAnexoLocalNome" x-cloak class="text-xs text-slate-500 dark:text-slate-400">{{ __('(ainda não guardado — use «Guardar número e data»)') }}</span>
                                        <a
                                            x-show="!protoAnexoLocalNome && protoAnexoUrl && protoAnexoNome"
                                            x-cloak
                                            :href="protoAnexoUrl"
                                            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            title="{{ __('Visualizar') }}"
                                            aria-label="{{ __('Visualizar') }}"
                                        >
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </a>
                                        <a
                                            x-show="!protoAnexoLocalNome && protoAnexoUrl && protoAnexoNome"
                                            x-cloak
                                            :href="protoAnexoUrl"
                                            class="max-w-[min(100%,18rem)] truncate text-sm font-medium text-indigo-600 underline decoration-indigo-600/30 underline-offset-2 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            x-text="protoAnexoNome"
                                        >{{ $processo->marinha_protocolo_anexo_original_name }}</a>
                                        <span x-show="!protoAnexoLocalNome && protoAnexoUrl && protoAnexoNome" x-cloak class="text-xs text-slate-500 dark:text-slate-400">{{ __('(na ficha)') }}</span>
                                    </div>
                                    <label x-show="protoAnexoUrl" x-cloak class="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                        <input type="checkbox" name="remover_marinha_protocolo_anexo" value="1" class="rounded border-slate-300 text-violet-600 shadow-sm focus:ring-violet-500 dark:border-slate-600 dark:bg-slate-800 dark:focus:ring-violet-500" />
                                        <span>{{ __('Remover o comprovativo anexado') }}</span>
                                    </label>
                                </div>
                                <p x-show="protoOk" x-cloak class="mt-2 text-sm font-medium text-emerald-700 dark:text-emerald-400" x-text="protoOk"></p>
                                <p x-show="protoErrNumero" x-cloak class="mt-2 text-sm text-red-600 dark:text-red-400" x-text="protoErrNumero"></p>
                                <p x-show="protoErrData" x-cloak class="mt-2 text-sm text-red-600 dark:text-red-400" x-text="protoErrData"></p>
                                <p x-show="protoErrAnexo" x-cloak class="mt-2 text-sm text-red-600 dark:text-red-400" x-text="protoErrAnexo"></p>
                            </form>
                        @else
                            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="text-slate-500 dark:text-slate-400">{{ __('Número de protocolo') }}</dt>
                                    <dd class="mt-0.5 font-medium text-slate-900 dark:text-slate-100">{{ filled($processo->marinha_protocolo_numero) ? $processo->marinha_protocolo_numero : '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-slate-500 dark:text-slate-400">{{ __('Data da protocolação') }}</dt>
                                    <dd class="mt-0.5 font-medium text-slate-900 dark:text-slate-100">{{ $processo->marinha_protocolo_data?->format('d/m/Y') ?? '—' }}</dd>
                                </div>
                                @if (filled($processo->marinha_protocolo_anexo_path))
                                    <div class="sm:col-span-2">
                                        <dt class="text-slate-500 dark:text-slate-400">{{ __('Comprovativo do protocolo') }}</dt>
                                        <dd class="mt-0.5">
                                            <a href="{{ route('processos.protocolo-marinha.anexo', $processo) }}" class="font-medium text-indigo-600 underline decoration-indigo-600/30 underline-offset-2 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300" target="_blank" rel="noopener noreferrer">{{ $processo->marinha_protocolo_anexo_original_name ?: __('Descarregar') }}</a>
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        @endcan
                    </section>
                @endif

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
                        <svg class="h-6 w-6 shrink-0 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 4.5h12M3.75 6.75h.008v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.008v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 4.5h.008v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
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
                                $nxChecklistIconeVerdeFicheiro = $st === \App\Enums\ProcessoDocumentoStatus::Fisico
                                    || ($st === \App\Enums\ProcessoDocumentoStatus::Enviado && $doc->anexos->isNotEmpty());
                                $nxChecklistIconeTitulo = $nxChecklistIconeVerdeFicheiro
                                    ? $st->label()
                                    : ($st === \App\Enums\ProcessoDocumentoStatus::Enviado
                                        ? __('Enviado sem ficheiro anexado nesta linha; o visto verde aparece após anexar ou marcar como físico.')
                                        : $st->label());
                            @endphp
                            <li class="px-5 py-4 sm:px-7">
                                <div class="flex items-start gap-3 sm:gap-4">
                                    <span
                                        class="relative mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center"
                                        title="{{ $nxChecklistIconeTitulo }}"
                                        aria-label="{{ $nxChecklistIconeTitulo }}"
                                    >
                                        @if ($nxChecklistIconeVerdeFicheiro)
                                            <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        @elseif ($st === \App\Enums\ProcessoDocumentoStatus::Dispensado)
                                            <svg class="h-6 w-6 text-slate-400 dark:text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />
                                            </svg>
                                        @else
                                            <svg class="h-6 w-6 text-amber-500 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        @endif
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-start gap-x-2 gap-y-1.5 py-2">
                                            <p class="min-w-0 flex-1 text-sm font-medium leading-snug text-slate-800 dark:text-slate-200">
                                                {{ $doc->documentoTipo->nome }}
                                            </p>
                                            @if ($st === \App\Enums\ProcessoDocumentoStatus::Fisico)
                                                <span
                                                    class="inline-flex shrink-0 items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-900 shadow-sm dark:border-emerald-800/50 dark:bg-emerald-950/45 dark:text-emerald-100"
                                                    title="{{ __('Documento marcado como entrega física (em papel ou presencial), sem anexo digital nesta linha.') }}"
                                                >
                                                    {{ __('Físico') }}
                                                </span>
                                            @elseif ($st === \App\Enums\ProcessoDocumentoStatus::Enviado && $doc->anexos->isNotEmpty())
                                                <span
                                                    class="inline-flex shrink-0 items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-900 shadow-sm dark:border-emerald-800/50 dark:bg-emerald-950/45 dark:text-emerald-100"
                                                    title="{{ __('Documento com ficheiro anexado nesta linha.') }}"
                                                >
                                                    {{ __('Anexado') }}
                                                </span>
                                            @endif
                                        </div>
                                        @if (\App\Support\ChaChecklistDocumentoCodigos::isCnhComValidade($codigoTipo))
                                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Se existir CNH na ficha do cliente, ela é copiada para este processo ao abrir a ficha. Pode também anexar outra cópia abaixo.') }}</p>
                                        @endif
                                        @if ($isResidenciaItem || $codigoTipo === \App\Support\Normam211DocumentoCodigos::CHA_COMPROVANTE_RESIDENCIA_212_1C_LEGACY)
                                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Se existir comprovante de endereço na ficha do cliente, ele é copiado automaticamente para este processo (exceto se usar só a declaração por modelo).') }}</p>
                                        @endif
                                        @if ($codigoTipo === 'CHA_CARTEIRA_EXISTENTE' || $codigoTipo === 'CHA_OU_DECL_EXTRAVIO_5D')
                                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Se a CHA estiver anexada na habilitação do cliente, a cópia é trazida para este processo ao abrir a ficha.') }}</p>
                                        @endif
                                        @if (\App\Support\ChecklistDocumentoMultiplosAnexos::permite($codigoTipo))
                                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Se a embarcação do processo já tiver foto de través e de popa na ficha, este item fica concluído; pode anexar mais fotos aqui se precisar.') }}</p>
                                        @endif
                                        @if (\App\Support\ChaChecklistDocumentoCodigos::isAtestadoMedicoPsicofisico($codigoTipo) && $st === \App\Enums\ProcessoDocumentoStatus::Dispensado)
                                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Dispensado automaticamente: há CNH anexada no processo ou na ficha do cliente.') }}</p>
                                        @endif
                                    </div>
                                    <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                                        @if ($st === \App\Enums\ProcessoDocumentoStatus::Pendente)
                                            @can('updateDocumento', $processo)
                                                @if ($urlModeloRender && $doc->preenchido_via_modelo)
                                                    <a
                                                        href="{{ $urlModeloRender }}"
                                                        target="_blank"
                                                        rel="noopener"
                                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-900 shadow-sm hover:bg-indigo-100 dark:border-indigo-900/40 dark:bg-indigo-950/50 dark:text-indigo-200"
                                                        title="{{ __('Abrir modelo') }}"
                                                        aria-label="{{ __('Abrir modelo') }}"
                                                    >
                                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                    </a>
                                                    @if ($nxFichaChecklistMostrarBaixarModelo)
                                                        @php
                                                            $urlModeloPrint = $urlModeloRender.(str_contains($urlModeloRender, '?') ? '&' : '?').'print=1';
                                                            $urlModeloPdf = $urlModeloRender.(str_contains($urlModeloRender, '?') ? '&' : '?').'format=pdf';
                                                            $urlModeloDoc = $urlModeloRender.(str_contains($urlModeloRender, '?') ? '&' : '?').'format=doc';
                                                            $nxBaixarId = 'nx-baixar-modelo-'.$doc->id;
                                                        @endphp
                                                        <label class="relative inline-flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" title="{{ __('Baixar como PDF, DOC ou para impressão') }}">
                                                            <svg class="pointer-events-none h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                                            <select
                                                                id="{{ $nxBaixarId }}"
                                                                aria-label="{{ __('Baixar como PDF, DOC ou para impressão') }}"
                                                                class="absolute inset-0 h-full w-full cursor-pointer appearance-none opacity-0"
                                                                onchange="const v=this.value; if(v){ window.open(v,'_blank','noopener'); } this.selectedIndex=0;"
                                                            >
                                                                <option value="" selected>{{ __('Baixar') }}</option>
                                                                <option value="{{ $urlModeloPdf }}">{{ __('PDF') }}</option>
                                                                <option value="{{ $urlModeloDoc }}">{{ __('DOC') }}</option>
                                                                <option value="{{ $urlModeloPrint }}">{{ __('Imprimir') }}</option>
                                                            </select>
                                                        </label>
                                                    @endif
                                                @endif
                                                @if (\App\Support\ChecklistDocumentoModelo::tipoTemModelo($doc->documentoTipo) && ! $isResidenciaItem && ! $isAnexo5hItem && ! $isAnexo5dItem && ! $isAnexo3dItem)
                                                    <form method="POST" action="{{ route('processos.documentos.update', [$processo, $doc]) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ \App\Enums\ProcessoDocumentoStatus::Enviado->value }}" />
                                                        <input type="hidden" name="preenchido_via_modelo" value="1" />
                                                        <button type="submit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-teal-200 bg-teal-50 text-teal-900 shadow-sm hover:bg-teal-100 dark:border-teal-900/40 dark:bg-teal-950/50 dark:text-teal-200" title="{{ __('Conclui o item pelo preenchimento digital do modelo. Para ver o documento, use Visualizar ou Abrir modelo. Para anexar o ficheiro assinado ou entrega em papel, use Anexar ou Físico.') }}" aria-label="{{ __('Gerar') }}">
                                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if ($isResidenciaItem)
                                                    <form method="POST" action="{{ route('processos.documentos.update', [$processo, $doc]) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ \App\Enums\ProcessoDocumentoStatus::Enviado->value }}" />
                                                        <input type="hidden" name="declaracao_residencia_2g" value="1" />
                                                        <button type="submit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-sky-200 bg-sky-50 text-sky-900 shadow-sm hover:bg-sky-100 dark:border-sky-900/40 dark:bg-sky-950/50 dark:text-sky-200" title="{{ __('Declaração') }}" aria-label="{{ __('Declaração') }}">
                                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if ($isAnexo5hItem)
                                                    <form method="POST" action="{{ route('processos.documentos.update', [$processo, $doc]) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ \App\Enums\ProcessoDocumentoStatus::Enviado->value }}" />
                                                        <input type="hidden" name="declaracao_anexo_5h" value="1" />
                                                        <button type="submit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-violet-200 bg-violet-50 text-violet-900 shadow-sm hover:bg-violet-100 dark:border-violet-900/40 dark:bg-violet-950/50 dark:text-violet-200" title="{{ __('Requerimento') }}" aria-label="{{ __('Requerimento') }}">
                                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if ($isAnexo5dItem)
                                                    <form method="POST" action="{{ route('processos.documentos.update', [$processo, $doc]) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ \App\Enums\ProcessoDocumentoStatus::Enviado->value }}" />
                                                        <input type="hidden" name="declaracao_anexo_5d" value="1" />
                                                        <button type="submit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-violet-200 bg-violet-50 text-violet-900 shadow-sm hover:bg-violet-100 dark:border-violet-900/40 dark:bg-violet-950/50 dark:text-violet-200" title="{{ __('Declaração') }}" aria-label="{{ __('Declaração') }}">
                                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if ($isAnexo3dItem)
                                                    <form method="POST" action="{{ route('processos.documentos.update', [$processo, $doc]) }}" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ \App\Enums\ProcessoDocumentoStatus::Enviado->value }}" />
                                                        <input type="hidden" name="declaracao_anexo_3d" value="1" />
                                                        <button type="submit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-violet-200 bg-violet-50 text-violet-900 shadow-sm hover:bg-violet-100 dark:border-violet-900/40 dark:bg-violet-950/50 dark:text-violet-200" title="{{ __('Declaração') }} (NORMAM 212)" aria-label="{{ __('Declaração') }} (NORMAM 212)">
                                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('processos.documentos.anexos.store', [$processo, $doc]) }}" enctype="multipart/form-data" class="inline">
                                                    @csrf
                                                    <input type="file" name="arquivos[]" multiple class="hidden" accept="{{ \App\Support\ChecklistDocumentoMultiplosAnexos::permite($codigoTipo) ? '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp' : '.pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,application/pdf,image/*' }}" id="nx-ficha-file-{{ $doc->id }}" onchange="this.form.requestSubmit()" />
                                                    <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" title="{{ __('Anexar') }}" aria-label="{{ __('Anexar') }}" onclick="document.getElementById('nx-ficha-file-{{ $doc->id }}').click()">
                                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.009-.01-.01m7.364-7.364L12 10.5" /></svg>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('processos.documentos.update', [$processo, $doc]) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ \App\Enums\ProcessoDocumentoStatus::Fisico->value }}" />
                                                    <button type="submit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-900 shadow-sm hover:bg-indigo-100 dark:border-indigo-900/40 dark:bg-indigo-950/50 dark:text-indigo-200" title="{{ __('Físico') }}" aria-label="{{ __('Físico') }}">
                                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" /><path d="M14 2v6h6" /><path d="M10 9H8M16 13H8M16 17H8M16 11H8M16 15H8M13 19H8" /></svg>
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
                                                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                                                                title="{{ $anexo->nome_original }}"
                                                                aria-label="{{ __('Visualizar') }}: {{ $anexo->nome_original }}"
                                                            >
                                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                            </a>
                                                            @can('updateDocumento', $processo)
                                                                <form
                                                                    method="POST"
                                                                    action="{{ $anexo->opaqueDestroyUrl() }}"
                                                                    class="inline"
                                                                    onsubmit="return confirm(@json(__('Remover este anexo?')))"
                                                                >
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-900 shadow-sm hover:bg-red-100 dark:border-red-900/40 dark:bg-red-950/40 dark:text-red-200" title="{{ __('Remover') }}" aria-label="{{ __('Remover') }}">
                                                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
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
                                                            <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" title="{{ __('Anexar mais') }}" aria-label="{{ __('Anexar mais') }}" onclick="document.getElementById('nx-ficha-file-mais-{{ $doc->id }}').click()">
                                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            @else
                                                <div class="flex max-w-md flex-col items-end gap-2">
                                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                                        <a
                                                            href="{{ $doc->anexos->first()->urlPublica() }}"
                                                            target="_blank"
                                                            rel="noopener"
                                                            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                                                            title="{{ __('Visualizar') }}"
                                                            aria-label="{{ __('Visualizar') }}"
                                                        >
                                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                        </a>
                                                        @can('updateDocumento', $processo)
                                                            <form
                                                                method="POST"
                                                                action="{{ $doc->anexos->first()->opaqueDestroyUrl() }}"
                                                                class="inline"
                                                                onsubmit="return confirm(@json(__('Remover este anexo?')))"
                                                            >
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-900 shadow-sm hover:bg-red-100 dark:border-red-900/40 dark:bg-red-950/40 dark:text-red-200" title="{{ __('Excluir anexo') }}" aria-label="{{ __('Excluir anexo') }}">
                                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </div>
                                            @endif
                                        @elseif ($urlVisualizarModeloLinha)
                                            <a
                                                href="{{ $urlVisualizarModeloLinha }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-900 shadow-sm hover:bg-indigo-100 dark:border-indigo-900/40 dark:bg-indigo-950/50 dark:text-indigo-200"
                                                title="{{ __('Abrir modelo') }}"
                                                aria-label="{{ __('Abrir modelo') }}"
                                            >
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                            </a>
                                            @if ($nxFichaChecklistMostrarBaixarModelo)
                                                @php
                                                    $urlModeloPrint = $urlVisualizarModeloLinha.(str_contains($urlVisualizarModeloLinha, '?') ? '&' : '?').'print=1';
                                                    $urlModeloPdf = $urlVisualizarModeloLinha.(str_contains($urlVisualizarModeloLinha, '?') ? '&' : '?').'format=pdf';
                                                    $urlModeloDoc = $urlVisualizarModeloLinha.(str_contains($urlVisualizarModeloLinha, '?') ? '&' : '?').'format=doc';
                                                    $nxBaixarId = 'nx-baixar-modelo-v-'.$doc->id;
                                                @endphp
                                                <label class="relative inline-flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" title="{{ __('Baixar como PDF, DOC ou para impressão') }}">
                                                    <svg class="pointer-events-none h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                                    <select
                                                        id="{{ $nxBaixarId }}"
                                                        aria-label="{{ __('Baixar como PDF, DOC ou para impressão') }}"
                                                        class="absolute inset-0 h-full w-full cursor-pointer appearance-none opacity-0"
                                                        onchange="const v=this.value; if(v){ window.open(v,'_blank','noopener'); } this.selectedIndex=0;"
                                                    >
                                                        <option value="" selected>{{ __('Baixar') }}</option>
                                                        <option value="{{ $urlModeloPdf }}">{{ __('PDF') }}</option>
                                                        <option value="{{ $urlModeloDoc }}">{{ __('DOC') }}</option>
                                                        <option value="{{ $urlModeloPrint }}">{{ __('Imprimir') }}</option>
                                                    </select>
                                                </label>
                                            @endif
                                            @can('updateDocumento', $processo)
                                                <form method="POST" action="{{ route('processos.documentos.anexos.store', [$processo, $doc]) }}" enctype="multipart/form-data" class="inline">
                                                    @csrf
                                                    <input type="file" name="arquivos[]" multiple class="hidden" accept="{{ \App\Support\ChecklistDocumentoMultiplosAnexos::permite($codigoTipo) ? '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp' : '.pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,application/pdf,image/*' }}" id="nx-ficha-file-modelo-{{ $doc->id }}" onchange="this.form.requestSubmit()" />
                                                    <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" title="{{ __('Anexar') }}" aria-label="{{ __('Anexar') }}" onclick="document.getElementById('nx-ficha-file-modelo-{{ $doc->id }}').click()">
                                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.009-.01-.01m7.364-7.364L12 10.5" /></svg>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('processos.documentos.update', [$processo, $doc]) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ \App\Enums\ProcessoDocumentoStatus::Fisico->value }}" />
                                                    <button type="submit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-900 shadow-sm hover:bg-indigo-100 dark:border-indigo-900/40 dark:bg-indigo-950/50 dark:text-indigo-200" title="{{ __('Físico') }}" aria-label="{{ __('Físico') }}">
                                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" /><path d="M14 2v6h6" /><path d="M10 9H8M16 13H8M16 17H8M16 11H8M16 15H8M13 19H8" /></svg>
                                                    </button>
                                                </form>
                                                @if ($nxFichaChecklistMostrarAnularCorrigirModelo)
                                                    <form
                                                        method="POST"
                                                        action="{{ route('processos.documentos.update', [$processo, $doc]) }}"
                                                        class="inline"
                                                        onsubmit="return confirm(@json(__('Anular o preenchimento por modelo e voltar a «Pendente»? Pode gerar de novo ou anexar outro documento.')))"
                                                    >
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ \App\Enums\ProcessoDocumentoStatus::Pendente->value }}" />
                                                        <button type="submit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-amber-200 bg-amber-50 text-amber-950 shadow-sm hover:bg-amber-100 dark:border-amber-900/40 dark:bg-amber-950/45 dark:text-amber-100" title="{{ __('Anular / corrigir') }}" aria-label="{{ __('Anular / corrigir') }}">
                                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                        @elseif ($st === \App\Enums\ProcessoDocumentoStatus::Dispensado && ! $isResidenciaItem && ! $isAnexo5hItem && ! $isAnexo5dItem && ! $isAnexo3dItem)
                                            @can('updateDocumento', $processo)
                                                <form method="POST" action="{{ route('processos.documentos.anexos.store', [$processo, $doc]) }}" enctype="multipart/form-data" class="inline">
                                                    @csrf
                                                    <input type="file" name="arquivos[]" multiple class="hidden" accept="{{ \App\Support\ChecklistDocumentoMultiplosAnexos::permite($codigoTipo) ? '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp' : '.pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,application/pdf,image/*' }}" id="nx-ficha-file-{{ $doc->id }}" onchange="this.form.requestSubmit()" />
                                                    <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-800 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800" title="{{ __('Anexar') }}" aria-label="{{ __('Anexar') }}" onclick="document.getElementById('nx-ficha-file-{{ $doc->id }}').click()">
                                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.009-.01-.01m7.364-7.364L12 10.5" /></svg>
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
                                                    <button type="submit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" title="{{ __('Trocar anexo') }}" aria-label="{{ __('Trocar anexo') }}">
                                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13.5V7.5H13.5M12 5.5l2.5 2-2.5 2" /><path d="M19 10.5v6H10.5M12 15l-2.5 1.5 2.5 1.5" /></svg>
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
