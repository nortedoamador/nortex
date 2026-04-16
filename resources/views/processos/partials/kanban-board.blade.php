@php
    use App\Enums\ProcessoStatus;

    $tituloSwalPendenciasKanban = $tituloSwalPendenciasKanban ?? __('Processo com pendências');
    $nxCienciaTextoSecundarioKanban = $nxCienciaTextoSecundarioKanban ?? __('Deseja realmente alterar o status mesmo assim?');

    $kanbanColDot = static fn (ProcessoStatus $s): string => $s->uiKanbanColumnDotClass();
@endphp
<div
    x-data="{
        podeMover: @json($podeMoverKanban),
        dragUrl: null,
        dragId: null,
        dragStatusAtual: null,
        dragDocsPendentes: false,
        colOver: null,
        dragCienciaLinha: '',
        dragCienciaFrase: '',
        dragCienciaTexto: '',
        dragCienciaTitulo: '',
        startDrag(e) {
            if (!this.podeMover) {
                e.preventDefault();
                return;
            }
            const el = e.currentTarget;
            this.dragUrl = el.dataset.statusUrl;
            this.dragId = el.dataset.processoId;
            this.dragStatusAtual = el.dataset.statusAtual || null;
            this.dragDocsPendentes = el.dataset.nxDocsPendentes === '1';
            this.dragCienciaLinha = el.dataset.cienciaLinha || '';
            this.dragCienciaFrase = el.dataset.cienciaFrasePendentes || '';
            this.dragCienciaTexto = el.dataset.cienciaTextoSecundario || '';
            this.dragCienciaTitulo = el.dataset.cienciaTitulo || '';
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', this.dragId);
            el.classList.add('opacity-50', 'ring-2', 'ring-indigo-400');
        },
        endDrag(e) {
            e.currentTarget.classList.remove('opacity-50', 'ring-2', 'ring-indigo-400');
            this.colOver = null;
            if (e.dataTransfer?.dropEffect === 'none') {
                this.dragUrl = null;
                this.dragId = null;
                this.dragStatusAtual = null;
                this.dragDocsPendentes = false;
                this.dragCienciaLinha = '';
                this.dragCienciaFrase = '';
                this.dragCienciaTexto = '';
                this.dragCienciaTitulo = '';
            }
        },
        async dropOnColumn(novoStatus) {
            if (!this.podeMover || !this.dragUrl) {
                this.dragUrl = null;
                return;
            }
            if (novoStatus === this.dragStatusAtual) {
                this.dragUrl = null;
                this.dragId = null;
                this.dragStatusAtual = null;
                this.dragDocsPendentes = false;
                this.dragCienciaLinha = '';
                this.dragCienciaFrase = '';
                this.dragCienciaTexto = '';
                this.dragCienciaTitulo = '';
                return;
            }
            let confirmarCiencia = false;
            if (this.dragDocsPendentes && novoStatus !== this.dragStatusAtual) {
                let ok = false;
                if (typeof window.nxSwalConfirmarCienciaDocumental === 'function') {
                    const r = await window.nxSwalConfirmarCienciaDocumental({
                        titulo: this.dragCienciaTitulo || @json($tituloSwalPendenciasKanban),
                        linhaProcesso: this.dragCienciaLinha,
                        frasePendentes: this.dragCienciaFrase,
                        textoSecundario: this.dragCienciaTexto || @json($nxCienciaTextoSecundarioKanban),
                    });
                    ok = r.isConfirmed === true;
                } else {
                    ok = window.confirm(this.dragCienciaFrase + '\n\n' + (this.dragCienciaTexto || @json($nxCienciaTextoSecundarioKanban)));
                }
                if (!ok) {
                    this.dragUrl = null;
                    this.dragId = null;
                    this.dragStatusAtual = null;
                    this.dragDocsPendentes = false;
                    this.dragCienciaLinha = '';
                    this.dragCienciaFrase = '';
                    this.dragCienciaTexto = '';
                    this.dragCienciaTitulo = '';
                    return;
                }
                confirmarCiencia = true;
            }
            const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const res = await fetch(this.dragUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    status: novoStatus,
                    confirmar_ciencia_pendencias_documentais: confirmarCiencia,
                }),
            });
            this.dragUrl = null;
            this.dragId = null;
            this.dragStatusAtual = null;
            this.dragDocsPendentes = false;
            this.dragCienciaLinha = '';
            this.dragCienciaFrase = '';
            this.dragCienciaTexto = '';
            this.dragCienciaTitulo = '';
            if (res.ok) {
                window.location.reload();
                return;
            }
            let msg = 'Não foi possível mover o processo.';
            try {
                const data = await res.json();
                msg = data.message || data.errors?.status?.[0] || msg;
            } catch (_) {}
            if (window.Swal) {
                const d = document.documentElement.classList.contains('dark');
                await window.Swal.fire({
                    icon: 'error',
                    title: 'Não foi possível mover o processo',
                    text: msg,
                    confirmButtonColor: '#4f46e5',
                    background: d ? '#0f172a' : '#fff',
                    color: d ? '#f1f5f9' : '#1e293b',
                });
            } else {
                alert(msg);
            }
        },
    }"
>
    <div class="w-full max-w-[1600px] mx-auto">
        <p class="mb-4 text-sm text-slate-600 dark:text-slate-400">
            @if ($podeMoverKanban)
                {{ __('Pode alterar a etapa aqui arrastando o cartão para outra coluna — ou na lista de processos, na ficha e em lote (menu da lista). Se houver documentos obrigatórios pendentes ao sair de «Em montagem», será pedida confirmação de ciência.') }}
            @else
                {{ __('Você não tem permissão para mover cartões neste quadro; na lista ou na ficha do processo pode alterar a etapa se tiver permissão.') }}
            @endif
        </p>

        <div class="flex gap-3 overflow-x-auto pb-2">
            @foreach ($colunas as $status)
                @php
                    $lista = $processos->get($status->value, collect());
                @endphp
                <div
                    class="min-w-[260px] max-w-[280px] flex-shrink-0 rounded-xl border border-slate-200/80 bg-slate-100/80 transition-colors dark:border-slate-700 dark:bg-slate-800/60"
                    :class="{ 'ring-2 ring-indigo-400 bg-indigo-50/50 dark:bg-indigo-950/40': colOver === '{{ $status->value }}' }"
                    @dragover.prevent="if (podeMover) { colOver = '{{ $status->value }}'; $event.dataTransfer.dropEffect = 'move'; }"
                    @dragleave.prevent="if ($event.currentTarget.contains($event.relatedTarget)) return; colOver = null"
                    @drop.prevent="colOver = null; dropOnColumn('{{ $status->value }}')"
                >
                    <div class="rounded-t-xl border-b border-slate-200/80 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 shrink-0 rounded-full {{ $kanbanColDot($status) }}" aria-hidden="true"></span>
                            <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $status->label() }}</span>
                            <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">({{ $lista->count() }})</span>
                        </div>
                    </div>
                    <div class="p-2 space-y-2 max-h-[70vh] overflow-y-auto min-h-[120px]">
                        @forelse ($lista as $processo)
                            @php
                                $nxNKanban = (int) ($processo->nx_docs_pendentes_count ?? 0);
                                $nxFraseKanban = trans_choice('{1} :count documento obrigatório pendente|[2,*] :count documentos obrigatórios pendentes', $nxNKanban, ['count' => $nxNKanban]);
                                $nxLinhaKanban = ($processo->tipoProcesso?->nome ?? __('Processo')).' — '.($processo->cliente?->nome ?? __('Sem cliente'));
                            @endphp
                            <div
                                @if ($podeMoverKanban)
                                    draggable="true"
                                @endif
                                data-processo-id="{{ $processo->id }}"
                                data-status-url="{{ route('processos.status', $processo) }}"
                                data-status-atual="{{ $processo->status->value }}"
                                data-nx-docs-pendentes="{{ ($nxNKanban > 0 && $processo->status === ProcessoStatus::EmMontagem) ? '1' : '0' }}"
                                data-ciencia-linha="{{ e($nxLinhaKanban) }}"
                                data-ciencia-frase-pendentes="{{ e($nxFraseKanban) }}"
                                data-ciencia-texto-secundario="{{ e($nxCienciaTextoSecundarioKanban) }}"
                                data-ciencia-titulo="{{ e($tituloSwalPendenciasKanban) }}"
                                @dragstart="startDrag($event)"
                                @dragend="endDrag($event)"
                                class="rounded-lg border border-slate-200/80 bg-white p-3 shadow-sm transition hover:border-indigo-300 dark:border-slate-700 dark:bg-slate-900 @if ($podeMoverKanban) cursor-grab active:cursor-grabbing @else cursor-default @endif"
                            >
                                <div class="min-w-0">
                                    <a
                                        href="{{ route('processos.show', $processo) }}"
                                        draggable="false"
                                        class="block rounded-md outline-none hover:text-indigo-700 focus-visible:ring-2 focus-visible:ring-indigo-500 dark:hover:text-indigo-300"
                                    >
                                        <div class="line-clamp-2 text-sm font-semibold text-slate-900 dark:text-slate-100">
                                            {{ $processo->tipoProcesso?->nome ?? __('Processo') }}
                                        </div>
                                        <div class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                                            {{ $processo->cliente?->nome ?? __('Sem cliente') }}
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-500">
                                            {{ __('Nº') }} {{ $processo->id }}
                                        </div>
                                    </a>
                                </div>
                                <div class="mt-3 flex items-end justify-between gap-2">
                                    <div class="min-w-0 shrink-0" @mousedown.stop @click.stop>
                                        @can('delete', $processo)
                                            @if ($processo->status === ProcessoStatus::EmMontagem)
                                                <form
                                                    method="POST"
                                                    action="{{ route('processos.destroy', $processo) }}"
                                                    class="inline"
                                                    data-nx-destroy-processo="1"
                                                    data-nx-processo-linha="{{ e($nxLinhaKanban) }}"
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
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-950/50 dark:hover:text-red-300"
                                                        title="{{ __('Excluir processo') }}"
                                                    >
                                                        <span class="sr-only">{{ __('Excluir processo') }}</span>
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                    <div class="flex flex-1 items-center justify-end gap-1.5 self-end pointer-events-none">
                                        @if ($processo->faltaIdentificacaoProtocoloMarinha())
                                            <span
                                                class="inline-flex shrink-0 text-amber-500 dark:text-amber-400"
                                                title="{{ __('Falta indicar o número de protocolo da Marinha.') }}"
                                            >
                                                <x-processo-protocolo-marinha-alerta-icon class="h-4 w-4" />
                                            </span>
                                        @endif
                                        <x-processo-docs-pendente-badge :processo="$processo" compact short class="pointer-events-auto" />
                                    </div>
                                </div>
                            </div>
                        @empty
                                <p class="px-1 py-2 text-xs text-slate-400 dark:text-slate-500">{{ __('Nenhum processo.') }}</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
