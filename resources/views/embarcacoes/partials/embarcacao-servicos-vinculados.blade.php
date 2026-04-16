@php
    $servicosVisiveis = $embarcacao->processos->filter(fn ($p) => auth()->user()->can('view', $p))->values();
@endphp
<div class="border-b border-slate-200 bg-slate-50/60 px-3 py-2.5 dark:border-slate-800 dark:bg-slate-800/30 sm:px-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div class="flex min-w-0 flex-1 items-center gap-2">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-500 text-white shadow-sm ring-2 ring-indigo-500/20 dark:bg-indigo-600 dark:ring-indigo-400/20" aria-hidden="true">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                </svg>
            </span>
            <h3 class="min-w-0 truncate text-base font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Processos') }}</h3>
            <span class="inline-flex h-7 min-w-7 shrink-0 items-center justify-center rounded-full bg-white px-2 text-xs font-bold tabular-nums text-slate-600 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-700">{{ $servicosVisiveis->count() }}</span>
        </div>
        @can('create', \App\Models\Processo::class)
            @if ($modalNovoProcesso ?? false)
                <button
                    type="button"
                    class="shrink-0 text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
                    @click="
                        $store.novoProcesso.preset = {
                            origemFichaEmbarcacao: true,
                            categoria: 'embarcacao',
                            clienteId: @js($embarcacao->cliente_id),
                            clienteRouteKey: @js($embarcacao->cliente?->getRouteKey()),
                            embarcacaoId: @js($embarcacao->id),
                            clienteDoc: @js($embarcacao->cliente ? ($embarcacao->cliente->documentoFormatado() ?? $embarcacao->cliente->cpf) : null),
                            clienteNome: @js($embarcacao->cliente?->nome),
                        };
                        $store.novoProcesso.open = true;
                    "
                >{{ __('Novo processo') }}</button>
            @else
                <a href="{{ route('processos.create') }}" class="shrink-0 text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">{{ __('Novo processo') }}</a>
            @endif
        @endcan
    </div>
</div>
<div class="max-h-[min(22rem,55vh)] overflow-y-auto px-3 py-3 sm:px-4">
    @if ($servicosVisiveis->isEmpty())
        <p class="text-center text-base leading-relaxed text-slate-500 dark:text-slate-400">{{ __('Nenhum serviço (processo) vinculado a esta embarcação.') }}</p>
    @else
        <ul class="space-y-3" role="list">
            @foreach ($servicosVisiveis as $processo)
                @php
                    $tipoNome = $processo->tipoProcesso?->nome ?? $processo->tipoProcessoTenant?->nome ?? __('Processo');
                    $temDocsObrigatoriosPendentes = app(\App\Services\ProcessoStatusService::class)->temDocumentoObrigatorioPendente($processo);
                @endphp
                <li class="rounded-lg border border-slate-200/90 bg-white p-3 dark:border-slate-700 dark:bg-slate-900/50">
                    <p class="text-base font-semibold leading-snug text-slate-900 dark:text-slate-100">
                        <a href="{{ route('processos.show', $processo) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $tipoNome }}</a>
                    </p>
                    <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
                        <span class="font-mono tabular-nums">{{ __('Nº') }} {{ $processo->id }}</span>
                        @if ($processo->cliente)
                            <span class="mx-1 text-slate-300 dark:text-slate-600" aria-hidden="true">·</span>
                            <span class="break-words">{{ $processo->cliente->nome }}</span>
                        @endif
                    </p>
                    @php
                        $tz = config('app.timezone');
                        $criado = $processo->created_at?->timezone($tz);
                        $atualizado = $processo->updated_at?->timezone($tz);
                    @endphp
                    @if ($criado)
                        <p class="mt-2 space-y-0.5 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                            @if ($atualizado && $atualizado->greaterThan($criado))
                                <span class="block tabular-nums">{{ __('Abertura') }}: {{ $criado->format('d/m/Y H:i') }}</span>
                                <span class="block tabular-nums">{{ __('Última atualização') }}: {{ $atualizado->format('d/m/Y H:i') }}</span>
                            @else
                                <span class="block tabular-nums">{{ __('Abertura') }}: {{ $criado->format('d/m/Y H:i') }}</span>
                            @endif
                        </p>
                    @endif
                    <div class="mt-2.5 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-violet-200 bg-violet-50 px-2.5 py-1 text-sm font-semibold text-violet-900 dark:border-violet-900/50 dark:bg-violet-950/50 dark:text-violet-200">
                            @if ($temDocsObrigatoriosPendentes)
                                <span
                                    class="inline-flex shrink-0"
                                    title="{{ __('Existem documentos obrigatórios pendentes no checklist deste processo.') }}"
                                >
                                    <span class="sr-only">{{ __('Documentos pendentes') }}:</span>
                                    <x-processo-docs-pendente-icon class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                                </span>
                            @endif
                            @if ($processo->faltaIdentificacaoProtocoloMarinha())
                                <span
                                    class="inline-flex shrink-0 text-amber-600 dark:text-amber-400"
                                    title="{{ __('Falta indicar o número de protocolo da Marinha.') }}"
                                >
                                    <span class="sr-only">{{ __('Protocolo da Marinha por indicar') }}:</span>
                                    <x-processo-protocolo-marinha-alerta-icon class="h-4 w-4" />
                                </span>
                            @endif
                            {{ $processo->status->label() }}
                        </span>
                        <a href="{{ route('processos.show', $processo) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">{{ __('Abrir') }}</a>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>
