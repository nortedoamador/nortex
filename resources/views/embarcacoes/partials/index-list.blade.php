<ul class="space-y-3">
    @forelse ($embarcacoes as $emb)
        @php
            $cpfDigits = preg_replace('/\D/', '', (string) ($emb->cpf ?? ''));
            $cpfFmt = $emb->cpf ?? null;
            if (strlen($cpfDigits) === 11) {
                $cpfFmt = substr($cpfDigits, 0, 3).'.'.substr($cpfDigits, 3, 3).'.'.substr($cpfDigits, 6, 3).'-'.substr($cpfDigits, 9, 2);
            }
            $sub = trim(($emb->tipo ?? '').($emb->tipo && $emb->inscricao ? ' · ' : '').($emb->inscricao ?? ''));
            $motorLinha = collect([$emb->marca_motor, $emb->potencia_maxima_motor])->filter(fn ($v) => filled($v))->implode(' · ');
        @endphp
        <li>
            <a
                href="{{ route('embarcacoes.show', $emb) }}"
                class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:border-indigo-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-900/50"
            >
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-white shadow-md shadow-indigo-600/25" aria-hidden="true">
                    @include('embarcacoes.partials.icon-tipo-embarcacao', ['tipo' => $emb->tipo, 'svgClass' => 'h-7 w-7'])
                </div>
                <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $emb->nome }}</span>
                            @php
                                $nxInscrita = filled($emb->inscricao);
                                $nxDataVenc = $emb->inscricao_data_vencimento;
                                $nxVencida = $nxDataVenc && $nxDataVenc->lt(\Carbon\Carbon::today());
                            @endphp
                            <span class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $nxInscrita ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200' : 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200' }}">
                                <span class="h-2 w-2 shrink-0 rounded-full {{ $nxInscrita ? 'bg-emerald-500' : 'bg-amber-400' }}" aria-hidden="true"></span>
                                {{ $nxInscrita ? __('Inscrita') : __('Sem inscrição') }}
                            </span>
                            @if ($nxDataVenc)
                                <span class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $nxVencida ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200' : 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200' }}">
                                    <span class="h-2 w-2 shrink-0 rounded-full {{ $nxVencida ? 'bg-red-500' : 'bg-emerald-500' }}" aria-hidden="true"></span>
                                    {{ $nxVencida ? __('Vencido') : __('Em vigor') }}
                                </span>
                            @endif
                        </div>
                        @if ($motorLinha !== '')
                            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $motorLinha }}</p>
                        @endif
                        @if ($sub !== '')
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ $sub }}</p>
                        @endif
                    </div>
                    <div class="min-w-0 shrink-0 text-left sm:max-w-[min(100%,14rem)] sm:text-right">
                        <span class="block truncate text-sm font-medium text-slate-700 dark:text-slate-300">{{ $emb->cliente?->nome ?? __('Sem cliente') }}</span>
                        @if ($cpfFmt)
                            <p class="mt-0.5 truncate font-mono text-xs text-slate-500 dark:text-slate-400">{{ $cpfFmt }}</p>
                        @endif
                    </div>
                </div>
                <svg class="h-5 w-5 shrink-0 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </li>
    @empty
        <li class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-14 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-400">
            @if (($busca ?? '') !== '' || ($tipo ?? '') !== '' || ($atividade ?? '') !== '' || ($construtor ?? '') !== '' || ($anoConstrucao ?? '') !== '' || ($numeroMotor ?? '') !== '')
                {{ __('Nenhum resultado para esta busca.') }}
            @else
                <p>{{ __('Nenhuma embarcação cadastrada.') }}</p>
                @can('create', \App\Models\Embarcacao::class)
                    <button
                        type="button"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                        @click="$store.novaEmbarcacao.open = true"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ __('Nova embarcação') }}
                    </button>
                @endcan
            @endif
        </li>
    @endforelse
</ul>
