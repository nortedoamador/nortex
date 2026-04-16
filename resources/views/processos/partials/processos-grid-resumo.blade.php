@php
    use App\Enums\ProcessoStatus;
@endphp

<div class="w-full max-w-[1600px] mx-auto">
    <div class="flex gap-3 overflow-x-auto pb-2">
        @foreach ($colunasGridResumo as $col)
            @php
                $key = $col['key'];
                $lista = $processosGrid->get($key, collect());
                $label = match ($key) {
                    'em_montagem' => ProcessoStatus::EmMontagem->label(),
                    'a_protocolar' => ProcessoStatus::AProtocolar->label(),
                    'concluido' => ProcessoStatus::Concluido->label(),
                    'pendente' => __('Pendente'),
                    'outras' => __('Demais etapas'),
                    default => $key,
                };
                $dot = $col['dot'] ?? 'bg-slate-400';
            @endphp
            <div class="min-w-[260px] max-w-[280px] flex-shrink-0 rounded-xl border border-slate-200/80 bg-slate-100/80 dark:border-slate-700 dark:bg-slate-800/60">
                <div class="rounded-t-xl border-b border-slate-200/80 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 shrink-0 rounded-full {{ $dot }}" aria-hidden="true"></span>
                        <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $label }}</span>
                        <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">({{ $lista->count() }})</span>
                    </div>
                </div>
                <div class="space-y-2 p-2 max-h-[70vh] min-h-[120px] overflow-y-auto">
                    @forelse ($lista as $processo)
                        @php
                            $nxLinhaKanban = ($processo->tipoProcesso?->nome ?? __('Processo')).' — '.($processo->cliente?->nome ?? __('Sem cliente'));
                        @endphp
                        <div class="rounded-lg border border-slate-200/80 bg-white p-3 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                            <a href="{{ route('processos.show', $processo) }}" class="block">
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
                            <div class="mt-3 flex items-end justify-between gap-2">
                                <div class="min-w-0 shrink-0" @click.stop>
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
                                <div class="flex flex-1 items-center justify-end gap-1.5 self-end">
                                    @if ($processo->faltaIdentificacaoProtocoloMarinha())
                                        <span
                                            class="inline-flex shrink-0 text-amber-500 dark:text-amber-400"
                                            title="{{ __('Falta indicar o número de protocolo da Marinha.') }}"
                                        >
                                            <x-processo-protocolo-marinha-alerta-icon class="h-4 w-4" />
                                        </span>
                                    @endif
                                    <x-processo-docs-pendente-badge :processo="$processo" compact short />
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
    <p class="mt-4 text-center text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('processos.kanban') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Quadro completo (todas as etapas)') }}</a>
    </p>
</div>
