<x-app-layout title="{{ __('Habilitações') }}">
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white sm:text-xl">{{ __('Habilitações') }}</h2>
                    <span
                        class="inline-flex w-fit max-w-[200px] shrink-0 items-center justify-center truncate rounded-full border border-emerald-300/90 bg-brand-softer px-3 py-1.5 text-center text-xs font-semibold text-fg-brand-strong shadow-sm ring-1 ring-emerald-200/80 dark:border-emerald-700/80 dark:bg-brand-softer-dark dark:text-fg-brand-strong-dark dark:ring-emerald-800/80"
                        x-data
                        x-text="$store?.nxHabilitacoes?.countText ?? @js((($busca ?? '') !== '' || ($clienteBusca ?? '') !== '' || ($categoria ?? '') !== '' || ($jurisdicao ?? '') !== '' || ($vigencia ?? '') !== '' ? trans_choice('{0} Nenhum resultado|{1} :count resultado encontrado|[2,*] :count resultados encontrados', (int) $habilitacoes->total(), ['count' => $habilitacoes->total()]) : trans_choice('{0} Nenhum cadastro de CHA|{1} :count cadastro|[2,*] :count cadastros', (int) $habilitacoes->total(), ['count' => $habilitacoes->total()])))"
                    >
                        @if (($busca ?? '') !== '' || ($clienteBusca ?? '') !== '' || ($categoria ?? '') !== '' || ($jurisdicao ?? '') !== '' || ($vigencia ?? '') !== '')
                            {{ trans_choice('{0} Nenhum resultado|{1} :count resultado encontrado|[2,*] :count resultados encontrados', (int) $habilitacoes->total(), ['count' => $habilitacoes->total()]) }}
                        @else
                            {{ trans_choice('{0} Nenhum cadastro de CHA|{1} :count cadastro|[2,*] :count cadastros', (int) $habilitacoes->total(), ['count' => $habilitacoes->total()]) }}
                        @endif
                    </span>
                </div>
            </div>
            @can('create', \App\Models\Habilitacao::class)
                <button
                    type="button"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:focus:ring-indigo-400/30"
                    @click="$store.novaHabilitacao.open = true"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Nova habilitação') }}
                </button>
            @endcan
        </div>
    </x-slot>

    @can('create', \App\Models\Habilitacao::class)
        <div class="hidden" x-data x-init="@if ($errors->any()) $store.novaHabilitacao.open = true @endif"></div>
        @include('habilitacoes.partials.modal-nova-habilitacao', [
            'clientes' => $clientes,
            'clientesSuggest' => $clientesSuggest,
        ])
    @endcan

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl space-y-6">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @cannot('create', \App\Models\Habilitacao::class)
                <p class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400">
                    {{ __('Você pode visualizar habilitações. Para cadastrar ou editar, é necessária a permissão «Gerir habilitações (CHA)».') }}
                </p>
            @endcannot

            <div
                x-data="nxHabilitacoesIndex({
                    baseUrl: @js(route('habilitacoes.index')),
                    initial: {
                        q: @js($busca ?? ''),
                        per_page: @js((int)($perPage ?? 5)),
                        cliente: @js((string)($clienteBusca ?? '')),
                        categoria: @js((string)($categoria ?? '')),
                        jurisdicao: @js((string)($jurisdicao ?? '')),
                        vigencia: @js((string)($vigencia ?? '')),
                        clientesSuggest: @js($clientesSuggest),
                    },
                })"
                x-init="init()"
                class="space-y-3"
            >
            <form method="GET" action="{{ route('habilitacoes.index') }}" class="space-y-3" @submit.prevent="apply()">
                @php
                    $temAlgo = ($busca ?? '') !== ''
                        || ($clienteBusca ?? '') !== ''
                        || ($categoria ?? '') !== ''
                        || ($jurisdicao ?? '') !== ''
                        || ($vigencia ?? '') !== ''
                        || ((int)($perPage ?? 5)) !== 5;
                @endphp

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-2">
                        <div class="relative shrink-0">
                            <select
                                name="per_page"
                                class="block w-full appearance-none rounded-full border border-slate-200 bg-white py-2.5 pl-4 pr-9 text-sm font-medium text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                                x-model.number="state.per_page"
                                @change="apply()"
                            >
                                @foreach ([5,10,20,50] as $pp)
                                    <option value="{{ $pp }}" @selected(((int)($perPage ?? 5)) === $pp)>{{ $pp }} {{ __('por página') }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </span>
                        </div>

                        @include('habilitacoes.partials.index-filtros-drawer')
                    </div>

                    <div class="relative min-w-0 flex-1">
                        <label class="sr-only" for="busca_habilitacoes">{{ __('Buscar habilitações') }}</label>
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400 dark:text-slate-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </span>
                        <input
                            id="busca_habilitacoes"
                            type="search"
                            name="q"
                            x-model="state.q"
                            @input.debounce.250ms="apply()"
                            placeholder="{{ __('Buscar por nome, CPF, cliente, CHA, jurisdição…') }}"
                            autocomplete="off"
                            class="w-full rounded-full border border-slate-200 bg-white py-3 pl-12 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500 {{ $temAlgo ? 'pr-24' : 'pr-4' }}"
                        />
                        @if ($temAlgo)
                            <a
                                href="{{ route('habilitacoes.index') }}"
                                class="absolute inset-y-0 right-3 my-auto flex h-8 items-center rounded-full px-3 text-xs font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                @click.prevent="reset()"
                            >{{ __('Limpar') }}</a>
                        @endif
                    </div>
                </div>

                <div
                    class="min-h-[1px]"
                    x-html="html.tags"
                    @nx-habilitacoes-remove-filter.window="removeFilter($event.detail?.key)"
                ></div>
            </form>

            <div x-html="html.list"></div>

            <div
                class="min-h-[1px]"
                x-html="html.pagination"
                @click.prevent="
                    const a = $event.target.closest('a');
                    if (a && a.href) { gotoPage(a.href); }
                "
            ></div>
            </div>
        </div>
    </div>

<script>
    (function () {
        const register = () => {
            Alpine.store('nxHabilitacoes', { countText: null });

            window.nxHabilitacoesIndex = ({ baseUrl, initial }) => ({
                state: {
                    q: initial.q ?? '',
                    per_page: initial.per_page ?? 5,
                    cliente: initial.cliente ?? '',
                    categoria: initial.categoria ?? '',
                    jurisdicao: initial.jurisdicao ?? '',
                    vigencia: initial.vigencia ?? '',
                },
                clientesSuggestList: Array.isArray(initial.clientesSuggest) ? initial.clientesSuggest : [],
                clienteHabOpen: false,
                clienteHabActive: -1,
                html: {
                    tags: @js(view('habilitacoes.partials.index-tags', [
                        'clienteBusca' => $clienteBusca ?? '',
                        'categoria' => $categoria ?? '',
                        'jurisdicao' => $jurisdicao ?? '',
                        'vigencia' => $vigencia ?? '',
                    ])->render()),
                    list: @js(view('habilitacoes.partials.index-list', [
                        'habilitacoes' => $habilitacoes,
                        'busca' => $busca ?? '',
                        'clienteBusca' => $clienteBusca ?? '',
                        'categoria' => $categoria ?? '',
                        'jurisdicao' => $jurisdicao ?? '',
                        'vigencia' => $vigencia ?? '',
                    ])->render()),
                    pagination: @js($habilitacoes->hasPages() ? (string) $habilitacoes->links() : ''),
                },
                aborter: null,
                init() {
                    Alpine.store('nxHabilitacoes').countText = @js(
                        ($busca ?? '') !== '' || ($clienteBusca ?? '') !== '' || ($categoria ?? '') !== '' || ($jurisdicao ?? '') !== '' || ($vigencia ?? '') !== ''
                            ? trans_choice('{0} Nenhum resultado|{1} :count resultado encontrado|[2,*] :count resultados encontrados', (int) $habilitacoes->total(), ['count' => $habilitacoes->total()])
                            : trans_choice('{0} Nenhum cadastro de CHA|{1} :count cadastro|[2,*] :count cadastros', (int) $habilitacoes->total(), ['count' => $habilitacoes->total()])
                    );
                },
                filtrosHabDrawerCount() {
                    let n = 0;
                    if ((this.state.cliente || '').trim() !== '') n++;
                    if ((this.state.categoria || '').trim() !== '') n++;
                    if ((this.state.jurisdicao || '').trim() !== '') n++;
                    if ((this.state.vigencia || '').trim() !== '') n++;
                    return n;
                },
                clienteHabMatches() {
                    const raw = (this.state.cliente || '').trim();
                    if (raw.length < 1) return [];
                    const qLower = raw.toLowerCase();
                    const qDigits = raw.replace(/\D/g, '');
                    const out = [];
                    const all = this.clientesSuggestList;
                    for (let i = 0; i < all.length && out.length < 60; i++) {
                        const item = all[i];
                        const nome = String(item.nome || '').toLowerCase();
                        const doc = String(item.doc || '').toLowerCase();
                        const dd = String(item.docDigits || '');
                        if (nome.includes(qLower) || doc.includes(qLower) || (qDigits.length > 0 && dd.includes(qDigits))) {
                            out.push(item);
                        }
                    }
                    return out;
                },
                onClienteHabFocus() {
                    this.clienteHabOpen = true;
                    this.clienteHabActive = -1;
                },
                onClienteHabBlur() {
                    setTimeout(() => {
                        this.clienteHabOpen = false;
                        this.clienteHabActive = -1;
                        this.apply();
                    }, 180);
                },
                pickClienteHab(item) {
                    this.state.cliente = item.nome || '';
                    this.clienteHabOpen = false;
                    this.clienteHabActive = -1;
                    this.apply();
                },
                onClienteHabKeydown(e) {
                    const list = this.clienteHabMatches();
                    if (e.key === 'Escape') {
                        if (this.clienteHabOpen) {
                            e.preventDefault();
                            this.clienteHabOpen = false;
                            this.clienteHabActive = -1;
                        }
                        return;
                    }
                    if (!list.length || (this.state.cliente || '').trim().length < 1) return;
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (!this.clienteHabOpen) this.clienteHabOpen = true;
                        this.clienteHabActive = this.clienteHabActive < list.length - 1 ? this.clienteHabActive + 1 : this.clienteHabActive;
                        if (this.clienteHabActive < 0) this.clienteHabActive = 0;
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (!this.clienteHabOpen) this.clienteHabOpen = true;
                        this.clienteHabActive = this.clienteHabActive > 0 ? this.clienteHabActive - 1 : 0;
                    } else if (e.key === 'Enter' && this.clienteHabOpen && this.clienteHabActive >= 0 && list[this.clienteHabActive]) {
                        e.preventDefault();
                        this.pickClienteHab(list[this.clienteHabActive]);
                    }
                },
                onVigenciaEmVigor(e) {
                    if (e.target.checked) {
                        this.state.vigencia = 'em_vigor';
                    } else if (this.state.vigencia === 'em_vigor') {
                        this.state.vigencia = '';
                    }
                    this.apply();
                },
                onVigenciaVencida(e) {
                    if (e.target.checked) {
                        this.state.vigencia = 'vencida';
                    } else if (this.state.vigencia === 'vencida') {
                        this.state.vigencia = '';
                    }
                    this.apply();
                },
                removeFilter(key) {
                    if (key === 'cliente') this.state.cliente = '';
                    if (key === 'categoria') this.state.categoria = '';
                    if (key === 'jurisdicao') this.state.jurisdicao = '';
                    if (key === 'vigencia') this.state.vigencia = '';
                    this.apply();
                },
                reset() {
                    this.state.q = '';
                    this.state.cliente = '';
                    this.state.categoria = '';
                    this.state.jurisdicao = '';
                    this.state.vigencia = '';
                    this.state.per_page = 5;
                    this.apply();
                },
                buildParams() {
                    const p = new URLSearchParams();
                    if (this.state.q) p.set('q', this.state.q);
                    if (this.state.cliente) p.set('cliente', this.state.cliente);
                    if (this.state.categoria) p.set('categoria', this.state.categoria);
                    if (this.state.jurisdicao) p.set('jurisdicao', this.state.jurisdicao);
                    if (this.state.vigencia) p.set('vigencia', this.state.vigencia);
                    if (this.state.per_page && Number(this.state.per_page) !== 5) p.set('per_page', String(this.state.per_page));
                    return p;
                },
                async apply(url = null) {
                    const params = this.buildParams();
                    const target = url ? new URL(url) : new URL(baseUrl);
                    if (!url) target.searchParams.delete('page');
                    params.forEach((v, k) => target.searchParams.append(k, v));
                    history.replaceState({}, '', target.toString());
                    try {
                        if (this.aborter) this.aborter.abort();
                        this.aborter = new AbortController();
                        const res = await fetch(target.toString(), {
                            headers: { Accept: 'application/json' },
                            signal: this.aborter.signal,
                        });
                        if (!res.ok) return;
                        const data = await res.json();
                        Alpine.store('nxHabilitacoes').countText = data.count_text ?? Alpine.store('nxHabilitacoes').countText;
                        this.html.tags = data.tags_html ?? this.html.tags;
                        this.html.list = data.list_html ?? this.html.list;
                        this.html.pagination = data.pagination_html ?? '';
                    } catch (err) {
                        /* abort / network */
                    }
                },
                gotoPage(href) {
                    this.apply(href);
                },
            });
        };

        if (window.Alpine) {
            register();
        } else {
            document.addEventListener('alpine:init', register, { once: true });
        }
    })();
</script>
</x-app-layout>
