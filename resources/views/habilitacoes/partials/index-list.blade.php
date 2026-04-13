<ul class="space-y-3">
    @forelse ($habilitacoes as $h)
        @php
            $cpfFmt = $h->cpfFormatadoTitular() ?? $h->cpf;
            $vencida = $h->data_validade && $h->data_validade->isPast();
        @endphp
        <li>
            <a
                href="{{ route('habilitacoes.show', $h) }}"
                class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:border-indigo-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-900/50"
            >
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-white shadow-md shadow-indigo-600/25">
                    @include('habilitacoes.partials.icon-cha-menu', ['svgClass' => 'h-7 w-7'])
                </div>
                <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $h->nome }}</span>
                            <span class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide
                                {{ $vencida ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200' : 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200' }}">
                                <span class="h-2 w-2 rounded-full {{ $vencida ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                                {{ $vencida ? __('Vencida') : __('Em vigor') }}
                            </span>
                        </div>
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $h->categoria }}</p>
                        @if ($h->numero_cha)
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('CHA: :n', ['n' => $h->numero_cha]) }}</p>
                        @endif
                    </div>
                    <div class="min-w-0 shrink-0 text-left sm:max-w-[min(100%,14rem)] sm:text-right">
                        <span class="block truncate text-sm font-medium text-slate-700 dark:text-slate-300">{{ $h->cliente?->nome ?? '—' }}</span>
                        @if ($cpfFmt)
                            <p class="mt-0.5 truncate font-mono text-xs text-slate-500 dark:text-slate-400">{{ $cpfFmt }}</p>
                        @endif
                        @if ($h->data_validade)
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Venc.: :d', ['d' => $h->data_validade->format('d/m/Y')]) }}</p>
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
            @if (($busca ?? '') !== '' || ($clienteBusca ?? '') !== '' || ($categoria ?? '') !== '' || ($jurisdicao ?? '') !== '' || ($vigencia ?? '') !== '')
                {{ __('Nenhum resultado para esta busca.') }}
            @else
                <p>{{ __('Nenhum cadastro de habilitação.') }}</p>
                @can('create', \App\Models\Habilitacao::class)
                    <button
                        type="button"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                        @click="$store.novaHabilitacao.open = true"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ __('Nova habilitação') }}
                    </button>
                @endcan
            @endif
        </li>
    @endforelse
</ul>
