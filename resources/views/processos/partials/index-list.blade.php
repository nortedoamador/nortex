@php
    use App\Enums\ProcessoStatus;

    $visualizacao = $visualizacao ?? 'list';
@endphp

@if (($visualizacao ?? 'list') === 'grid')
    @include('processos.partials.processos-grid-resumo', [
        'colunasGridResumo' => $colunasGridResumo,
        'processosGrid' => $processosGrid,
    ])
@else
    <ul class="space-y-4">
        @forelse ($processos as $processo)
            @php
                $st = $processo->status;
                $tipoNome = $processo->tipoProcesso?->nome ?? __('Processo');
                $clienteNome = $processo->cliente?->nome ?? __('Sem cliente');
                $linhaPrincipal = $tipoNome.' — '.$clienteNome;
                $nxNPendCiencia = (int) ($processo->nx_docs_pendentes_count ?? 0);
                $nxFraseCiencia = trans_choice('{1} :count documento obrigatório pendente|[2,*] :count documentos obrigatórios pendentes', $nxNPendCiencia, ['count' => $nxNPendCiencia]);
                $pendenteDocs = $nxNPendCiencia > 0;
                $atualizadoEm = $processo->updated_at
                    ? $processo->updated_at->timezone(config('app.timezone'))->format('d/m/Y - H:i')
                    : '—';
            @endphp
            <li class="nx-processo-list-item" style="--nx-card-i: {{ $loop->index }}">
                <div
                    class="group nx-processo-card relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-0 motion-safe:transition-all motion-safe:duration-300 motion-safe:ease-out motion-safe:hover:-translate-y-0.5 motion-safe:hover:border-slate-300/90 motion-safe:hover:shadow-md dark:border-slate-700/90 dark:bg-slate-900 dark:motion-safe:hover:border-slate-600 dark:motion-safe:hover:shadow-black/25 sm:flex-row"
                >
                    <div
                        class="nx-processo-card__accent w-1.5 shrink-0 motion-safe:transition-all motion-safe:duration-300 motion-safe:ease-out group-hover:w-2 group-hover:brightness-110 group-hover:saturate-110 {{ $st->uiListAccentBarClass() }}"
                        aria-hidden="true"
                    ></div>
                    @if (($mostrarSelecaoEmLote ?? false) && in_array($processo->id, $idsSelecaoLotePagina ?? [], true))
                        <div
                            class="flex shrink-0 items-center bg-slate-50/60 px-4 py-2 motion-safe:transition-colors motion-safe:duration-300 group-hover:bg-slate-100/80 dark:bg-slate-800/40 dark:group-hover:bg-slate-800/70 sm:px-2.5 sm:py-0"
                            @click.stop
                        >
                            <input
                                type="checkbox"
                                class="h-4 w-4 cursor-pointer rounded border-2 border-slate-400 text-indigo-600 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 dark:border-slate-500 dark:bg-slate-900 dark:focus:ring-offset-slate-900"
                                :checked="typeof isChecked === 'function' ? isChecked({{ $processo->id }}) : false"
                                @change="typeof toggle === 'function' ? toggle({{ $processo->id }}, $event.target.checked) : null"
                                aria-label="{{ __('Selecionar para ações em lote') }}"
                            />
                        </div>
                    @endif
                    <x-processo-tipo-icon
                        :processo="$processo"
                        class="mx-auto mt-4 shrink-0 self-center motion-safe:transition-transform motion-safe:duration-300 motion-safe:ease-out group-hover:scale-110 sm:mx-0 sm:mt-0 sm:ml-4"
                    />
                    <a
                        href="{{ route('processos.show', $processo) }}"
                        title="{{ __('Abrir ficha completa deste processo') }}"
                        class="flex min-w-0 flex-1 items-center gap-3 px-4 py-4 outline-none motion-safe:transition-colors motion-safe:duration-200 focus-visible:z-[1] focus-visible:rounded-lg focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-slate-900 sm:px-5"
                    >
                        <div class="flex min-w-0 flex-1 flex-col justify-center gap-2.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-bold leading-snug text-slate-900 motion-safe:transition-colors motion-safe:duration-200 group-hover:text-indigo-950 dark:text-slate-50 dark:group-hover:text-indigo-100 sm:text-[0.95rem]">{{ $tipoNome }}</span>
                                @if ($pendenteDocs)
                                    <span
                                        class="nx-processo-warn-icon inline-flex shrink-0 text-orange-500 dark:text-orange-400"
                                        title="{{ __('Existem documentos obrigatórios pendentes no checklist deste processo.') }}"
                                    >
                                        <x-processo-docs-pendente-icon class="h-5 w-5" />
                                    </span>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400 sm:text-[0.8125rem]">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="h-4 w-4 shrink-0 text-slate-400 motion-safe:transition-colors group-hover:text-indigo-400 dark:text-slate-500 dark:group-hover:text-indigo-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                    <span class="font-medium text-slate-600 motion-safe:transition-colors group-hover:text-slate-800 dark:text-slate-300 dark:group-hover:text-slate-100">{{ $clienteNome }}</span>
                                </span>
                                @if (filled($processo->jurisdicao))
                                    <span class="inline-flex min-w-0 items-center gap-1.5">
                                        <svg class="h-4 w-4 shrink-0 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.125-9 12.375-9 12.375S1.5 17.625 1.5 10.5a9 9 0 1 1 18 0Z" />
                                        </svg>
                                        <span class="min-w-0 truncate">{{ $processo->jurisdicao }}</span>
                                    </span>
                                @endif
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="h-4 w-4 shrink-0 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" />
                                    </svg>
                                    {{ $atualizadoEm }}
                                </span>
                            </div>
                        </div>
                        <span
                            class="pointer-events-none hidden shrink-0 items-center gap-1.5 rounded-full bg-indigo-100/90 px-3 py-1.5 text-xs font-semibold text-indigo-700 opacity-0 backdrop-blur-[2px] motion-safe:translate-x-2 motion-safe:transition-all motion-safe:duration-300 group-hover:translate-x-0 group-hover:opacity-100 dark:bg-indigo-950/55 dark:text-indigo-200 sm:inline-flex"
                            aria-hidden="true"
                        >
                            {{ __('Abrir') }}
                            <svg class="h-3.5 w-3.5 motion-safe:transition-transform motion-safe:duration-300 group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </span>
                    </a>
                    <div
                        class="flex w-full shrink-0 items-center gap-3 border-t border-slate-100 bg-slate-50/50 px-4 py-4 motion-safe:transition-[background-color] motion-safe:duration-300 group-hover:bg-slate-100/70 dark:border-slate-700/80 dark:bg-slate-800/30 dark:group-hover:bg-slate-800/55 sm:w-auto sm:border-l sm:border-t-0 sm:px-0 sm:py-3 sm:pl-3 sm:pr-4"
                        title="{{ __('Alterar status ou excluir rascunho') }}"
                    >
                        @if (($podeAlterarStatus ?? false))
                            <form
                                method="POST"
                                action="{{ route('processos.status', $processo) }}"
                                class="relative inline-flex items-center"
                                data-nx-status-ciencia-form="1"
                                data-nx-requer-ciencia="{{ ($pendenteDocs && $st === ProcessoStatus::EmMontagem) ? '1' : '0' }}"
                                data-nx-status-submit-on-change="1"
                                data-nx-status-atual="{{ $st->value }}"
                                data-nx-swal-titulo="{{ e($nxTituloSwalPendencias ?? __('Processo com pendências')) }}"
                                data-nx-ciencia-linha="{{ e($linhaPrincipal) }}"
                                data-nx-ciencia-frase-pendentes="{{ e($nxFraseCiencia) }}"
                                data-nx-ciencia-texto-secundario="{{ e($nxCienciaTextoSecundario ?? __('Deseja realmente alterar o status mesmo assim?')) }}"
                            >
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="confirmar_ciencia_pendencias_documentais" value="0" autocomplete="off" />
                                <label class="sr-only" for="status_proc_{{ $processo->id }}">{{ __('Alterar status') }}</label>
                                <div class="{{ $st->uiListSelectChromeWrapClass() }} mt-1 w-full sm:mt-0 sm:w-44">
                                    <select
                                        id="status_proc_{{ $processo->id }}"
                                        name="status"
                                        data-nx-processo-list-status="1"
                                        title="{{ __('Ao escolher outro status, o pedido é enviado automaticamente (pode ser pedida confirmação)') }}"
                                        class="nx-processo-list-status-select w-full min-w-0 cursor-pointer py-3 pl-4 pr-12 text-left text-sm font-semibold leading-snug motion-safe:transition-all motion-safe:duration-200 motion-safe:active:scale-[0.98] focus-visible:outline-none {{ $st->uiListSelectClasses() }}"
                                    >
                                        @foreach ($processo->statusesPermitidosParaAlteracao() as $opt)
                                            <option
                                                value="{{ $opt->value }}"
                                                style="{{ $opt->uiNativeSelectOptionStyle() }}"
                                                @selected($st === $opt)
                                            >{{ $opt->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <span class="pointer-events-none absolute right-3.5 top-1/2 z-10 -translate-y-1/2 {{ $st->uiListSelectChevronClass() }}" aria-hidden="true">
                                    <svg class="h-4 w-4 shrink-0 opacity-80" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </span>
                            </form>
                        @else
                            <div class="{{ $st->uiListSelectChromeWrapClass() }} mt-1 w-full sm:mt-0 sm:w-44" title="{{ __('Status atual (sem permissão para alterar aqui)') }}">
                                <span class="{{ $st->uiListReadonlyPillClasses() }} nx-processo-list-status-select pr-12">{{ $st->label() }}</span>
                            </div>
                        @endif
                        @can('delete', $processo)
                            @if ($st === ProcessoStatus::EmMontagem)
                                <form
                                    method="POST"
                                    action="{{ route('processos.destroy', $processo) }}"
                                    class="flex shrink-0 items-center"
                                    data-nx-destroy-processo="1"
                                    data-nx-processo-linha="{{ e($linhaPrincipal) }}"
                                    data-nx-excluir-titulo="{{ e(__('Excluir processo?')) }}"
                                    data-nx-excluir-aviso="{{ e(__('Os anexos enviados serão removidos. Esta ação não pode ser desfeita.')) }}"
                                    data-nx-excluir-pergunta="{{ e(__('Deseja realmente excluir este processo?')) }}"
                                    data-nx-excluir-btn-nao="{{ e(__('Não, desistir')) }}"
                                    data-nx-excluir-btn-sim="{{ e(__('Sim, excluir')) }}"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-0 bg-red-50 text-red-600 motion-safe:transition-[transform,background-color] motion-safe:duration-150 motion-safe:active:scale-90 hover:bg-red-100 hover:text-red-700 dark:bg-red-950/50 dark:text-red-400 dark:hover:bg-red-950/70"
                                        title="{{ __('Excluir este rascunho') }}"
                                    >
                                        <span class="sr-only">{{ __('Excluir processo') }}</span>
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        @endcan
                    </div>
                </div>
            </li>
        @empty
            <li class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-14 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-400">
                @if (($busca ?? '') !== '' || ($statusFiltro ?? '') !== '' || (($filtrosAvancados['avancados_ativos'] ?? 0) > 0))
                    {{ __('Nenhum processo para estes filtros.') }}
                @else
                    {{ __('Nenhum processo cadastrado.') }}
                @endif
                @can('create', \App\Models\Processo::class)
                    <div class="mt-4">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                            @click="$store.novoProcesso.preset = null; $store.novoProcesso.open = true"
                        >
                            {{ __('Novo processo') }}
                        </button>
                    </div>
                @endcan
            </li>
        @endforelse
    </ul>
@endif

