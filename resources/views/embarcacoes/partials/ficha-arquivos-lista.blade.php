@php
    $anexosArquivos = $embarcacao->anexos->reject(fn ($a) => in_array($a->tipo_codigo, [
        \App\Support\EmbarcacaoTiposAnexo::FOTO_TRAVES,
        \App\Support\EmbarcacaoTiposAnexo::FOTO_POPA,
        \App\Support\EmbarcacaoTiposAnexo::FOTO_OUTRAS,
    ], true))->values();
@endphp

<div class="min-w-0">
    <ul class="max-h-[min(28rem,70vh)] divide-y divide-slate-200 overflow-y-auto rounded-xl border border-slate-200/80 dark:divide-slate-800 dark:border-slate-700/80" role="list">
        @forelse ($anexosArquivos as $anexo)
            @php
                $mimeLower = strtolower((string) ($anexo->mime ?? ''));
                $isPdf = str_contains($mimeLower, 'pdf');
                $dataRef = $anexo->updated_at ?? $anexo->created_at;
                $tamanho = $anexo->tamanho;
                if (is_numeric($tamanho) && (int) $tamanho > 0) {
                    $__b = (int) $tamanho;
                    $__u = ['B', 'KB', 'MB', 'GB', 'TB'];
                    $__e = (int) floor(log($__b, 1024));
                    $__e = max(0, min($__e, count($__u) - 1));
                    $tamanhoFmt = round($__b / (1024 ** $__e), 1).' '.$__u[$__e];
                } else {
                    $tamanhoFmt = '—';
                }
            @endphp
            <li class="px-4 py-3 sm:px-5">
                <div class="flex gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/80" aria-hidden="true">
                        @if ($isPdf)
                            <span class="text-xs font-extrabold uppercase text-red-600 dark:text-red-400">PDF</span>
                        @else
                            <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100" title="{{ $anexo->nome_original }}">{{ $anexo->nome_original }}</p>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                            {{ $dataRef?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—' }}
                            <span class="text-slate-300 dark:text-slate-600">·</span>
                            {{ $tamanhoFmt }}
                        </p>
                        <div class="mt-1 flex flex-wrap items-center gap-1">
                            @if (filled($anexo->tipo_codigo))
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $anexo->tipoLabel() }}</span>
                            @endif
                            @php $vs = $anexo->extra_validation_status ?? \App\Enums\AnexoValidacaoStatus::Pendente; @endphp
                            <span class="inline-flex text-[11px] uppercase px-2 py-0.5 rounded-full
                                @if($vs->value === 'ok') bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200
                                @elseif($vs->value === 'pendente') bg-amber-100 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200
                                @elseif($vs->value === 'falhou') bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-200
                                @else bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 @endif">
                                {{ $vs->label() }}
                            </span>
                        </div>
                        @if ($anexo->extra_validation_notes)
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ $anexo->extra_validation_notes }}</p>
                        @endif
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                class="text-xs font-semibold uppercase tracking-wide text-violet-600 hover:underline dark:text-violet-400"
                                @click="previewId = previewId === {{ $anexo->id }} ? null : {{ $anexo->id }}"
                            >
                                <span x-text="previewId === {{ $anexo->id }} ? @js(__('Ocultar preview')) : @js(__('Preview'))"></span>
                            </button>
                            <x-anexo-list-icon-actions
                                class="!py-0"
                                :nova-aba-url="$anexo->signedInlineUrl()"
                                :download-url="$anexo->signedDownloadUrl()"
                                :print-url="$anexo->signedPrintUrl()"
                                :destroy-url="Auth::user()->can('manage', $embarcacao) ? $anexo->opaqueDestroyUrl() : null"
                            />
                        </div>
                        <div x-show="previewId === {{ $anexo->id }}" class="pt-2" x-cloak>
                            <x-anexo-preview :url="$anexo->signedInlineUrl()" :mime="$anexo->mime" :nome="$anexo->nome_original" />
                        </div>
                    </div>
                </div>
            </li>
        @empty
            <li class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400 sm:px-5">{{ __('Nenhum anexo.') }}</li>
        @endforelse
    </ul>
</div>
