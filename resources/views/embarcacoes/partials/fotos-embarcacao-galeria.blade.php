@php
    $mostrarTitulosVazios = $mostrarTitulosVazios ?? false;
    if (! isset($fotosGaleriaPaginator)) {
        $fotosTodas = \App\Support\EmbarcacaoFotosGaleria::itensOrdenados($embarcacao);
        $n = $fotosTodas->count();
        $fotosGaleriaPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $fotosTodas,
            $n,
            max(1, $n),
            1,
            ['path' => request()->url(), 'pageName' => 'fotos_page'],
        );
    }
@endphp

@if ($fotosGaleriaPaginator->total() === 0)
    @if ($mostrarTitulosVazios)
        <p class="text-center text-sm text-slate-500 dark:text-slate-400">{{ __('Nenhuma foto anexada.') }}</p>
    @endif
@else
    <div class="grid grid-cols-2 gap-2 sm:gap-3" role="list">
        @foreach ($fotosGaleriaPaginator as $item)
            @php
                $anexo = $item['anexo'];
                $mime = (string) ($anexo->mime ?? '');
                $ehImagem = str_starts_with($mime, 'image/');
                $urlInline = route('embarcacoes.anexos.inline', [$embarcacao, $anexo]);
            @endphp
            <div
                class="group relative aspect-[4/3] w-full overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200/90 dark:bg-slate-800 dark:ring-slate-700"
                role="listitem"
            >
                @if ($ehImagem)
                    <img
                        src="{{ $urlInline }}"
                        alt=""
                        class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                        loading="lazy"
                    />
                @else
                    <div class="flex h-full w-full flex-col items-center justify-center gap-1 bg-slate-200/80 p-2 text-center dark:bg-slate-800/80">
                        <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <span class="text-[10px] font-medium text-slate-600 dark:text-slate-300">{{ __('Ficheiro') }}</span>
                    </div>
                @endif

                <div class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent px-2 pb-2 pt-8">
                    <p class="flex flex-wrap items-center gap-1">
                        <span class="inline-flex max-w-full items-center gap-1 rounded-md bg-black/65 px-1.5 py-1 text-[10px] font-semibold leading-tight text-white shadow-sm ring-1 ring-white/10 backdrop-blur-sm sm:text-xs">
                            @if ($item['tipo'] === \App\Support\EmbarcacaoTiposAnexo::FOTO_POPA)
                                <svg class="h-3 w-3 shrink-0 text-amber-300 sm:h-3.5 sm:w-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.874a.563.563 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.563.563 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                </svg>
                            @endif
                            <span class="text-white">{{ $item['label'] }}</span>
                        </span>
                    </p>
                </div>

                <div
                    class="absolute inset-0 flex items-center justify-center gap-2 bg-black/0 opacity-0 transition duration-200 pointer-events-none group-hover:bg-black/45 group-hover:opacity-100 group-focus-within:bg-black/45 group-focus-within:opacity-100 group-hover:pointer-events-auto group-focus-within:pointer-events-auto sm:gap-3"
                >
                    <a
                        href="{{ $urlInline }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white !text-slate-900 text-slate-900 shadow-lg ring-1 ring-black/10 transition hover:bg-slate-50 hover:!text-slate-900 focus:outline-none focus:ring-2 focus:ring-violet-500"
                        title="{{ __('Abrir em nova aba') }}"
                        aria-label="{{ __('Ampliar foto') }}"
                    >
                        <svg class="h-5 w-5 shrink-0 text-slate-900" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                        </svg>
                    </a>
                    @can('update', $embarcacao)
                        {{-- type="button" + fetch: evita <form> dentro do formulário de edição (HTML proíbe; o browser fechava o <form> pai e o "Salvar" deixava de submeter). --}}
                        <button
                            type="button"
                            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-red-800/20 bg-red-600 !text-white text-white shadow-lg transition hover:bg-red-500 hover:!text-white focus:outline-none focus:ring-2 focus:ring-red-400"
                            data-nx-embarcacao-foto-delete
                            data-nx-url="{{ route('embarcacoes.anexos.destroy', [$embarcacao, $anexo]) }}"
                            data-nx-csrf="{{ csrf_token() }}"
                            data-nx-confirm="{{ e(__('Remover esta foto?')) }}"
                            aria-label="{{ __('Remover') }}"
                            title="{{ __('Remover') }}"
                        >
                            <svg class="h-5 w-5 shrink-0 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>
    @if ($fotosGaleriaPaginator->hasPages())
        <nav class="mt-3 border-t border-slate-200 pt-3 dark:border-slate-700" aria-label="{{ __('Paginação das fotos') }}">
            {{ $fotosGaleriaPaginator->onEachSide(1)->links() }}
        </nav>
    @endif
@endif
