<x-app-layout title="{{ __('Embarcações') }}">
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white sm:text-xl">{{ __('Embarcações') }}</h2>
                    <span
                        class="inline-flex w-fit max-w-[200px] shrink-0 items-center justify-center truncate rounded-full border border-emerald-300/90 bg-brand-softer px-3 py-1.5 text-center text-xs font-semibold text-fg-brand-strong shadow-sm ring-1 ring-emerald-200/80 dark:border-emerald-700/80 dark:bg-brand-softer-dark dark:text-fg-brand-strong-dark dark:ring-emerald-800/80"
                        x-data
                        x-text="$store?.nxEmbarcacoes?.countText ?? @js((($busca ?? '') !== '' || ($tipo ?? '') !== '' || ($atividade ?? '') !== '' || ($construtor ?? '') !== '' || ($anoConstrucao ?? '') !== '' || ($numeroMotor ?? '') !== '' ? trans_choice('{0} Nenhum resultado|{1} :count resultado encontrado|[2,*] :count resultados encontrados', (int) $embarcacoes->total(), ['count' => $embarcacoes->total()]) : trans_choice('{0} Nenhuma embarcação cadastrada|{1} :count cadastrada|[2,*] :count cadastradas', (int) $embarcacoes->total(), ['count' => $embarcacoes->total()])))"
                    >
                        @if (($busca ?? '') !== '' || ($tipo ?? '') !== '' || ($atividade ?? '') !== '' || ($construtor ?? '') !== '' || ($anoConstrucao ?? '') !== '' || ($numeroMotor ?? '') !== '')
                            {{ trans_choice('{0} Nenhum resultado|{1} :count resultado encontrado|[2,*] :count resultados encontrados', (int) $embarcacoes->total(), ['count' => $embarcacoes->total()]) }}
                        @else
                            {{ trans_choice('{0} Nenhuma embarcação cadastrada|{1} :count cadastrada|[2,*] :count cadastradas', (int) $embarcacoes->total(), ['count' => $embarcacoes->total()]) }}
                        @endif
                    </span>
                </div>
            </div>
            @can('create', \App\Models\Embarcacao::class)
                <button
                    type="button"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:focus:ring-indigo-400/30"
                    @click="$store.novaEmbarcacao.open = true"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Nova embarcação') }}
                </button>
            @endcan
        </div>
    </x-slot>

    @can('create', \App\Models\Embarcacao::class)
        <div class="hidden" x-data x-init="@if ($errors->any()) $store.novaEmbarcacao.open = true @endif"></div>
        @include('embarcacoes.partials.modal-nova-embarcacao', ['clientes' => $clientes])
    @endcan

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl space-y-6">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <div
                x-data="nxEmbarcacoesIndex({
                    baseUrl: @js(route('embarcacoes.index')),
                    initial: {
                        q: @js($busca ?? ''),
                        per_page: @js((int)($perPage ?? 5)),
                        tipo: @js((string)($tipo ?? '')),
                        atividade: @js((string)($atividade ?? '')),
                        construtor: @js((string)($construtor ?? '')),
                        ano_construcao: @js((string)($anoConstrucao ?? '')),
                        numero_motor: @js((string)($numeroMotor ?? '')),
                        sugestoesBusca: @js($sugestoesBuscaEmbarcacao ?? []),
                        construtores: @js($construtoresOptions ?? []),
                    },
                })"
                x-init="init()"
                class="space-y-3"
            >
            <form method="GET" action="{{ route('embarcacoes.index') }}" class="space-y-3" @submit.prevent="apply()">
                @php
                    $temAlgo = ($busca ?? '') !== ''
                        || ($tipo ?? '') !== ''
                        || ($atividade ?? '') !== ''
                        || ($construtor ?? '') !== ''
                        || ($anoConstrucao ?? '') !== ''
                        || ($numeroMotor ?? '') !== ''
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

                        @include('embarcacoes.partials.index-filtros-drawer')
                    </div>

                    <div class="relative min-w-0 flex-1">
                        <label class="sr-only" for="busca_embarcacoes">{{ __('Buscar embarcações') }}</label>
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400 dark:text-slate-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </span>
                        <input
                            id="busca_embarcacoes"
                            type="search"
                            name="q"
                            x-model="state.q"
                            autocomplete="off"
                            @focus="onEmbBuscaFocus()"
                            @blur="onEmbBuscaBlur()"
                            @keydown="onEmbBuscaKeydown($event)"
                            @input.debounce.250ms="apply()"
                            :aria-expanded="embBuscaOpen"
                            aria-autocomplete="list"
                            aria-controls="nx-emb-busca-sugestoes"
                            placeholder="{{ __('Cliente, embarcação ou inscrição…') }}"
                            class="w-full rounded-full border border-slate-200 bg-white py-3 pl-12 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500 {{ $temAlgo ? 'pr-24' : 'pr-4' }}"
                        />
                        <div
                            id="nx-emb-busca-sugestoes"
                            x-show="embBuscaOpen"
                            x-transition
                            x-cloak
                            class="absolute left-0 right-0 top-full z-50 mt-1 max-h-72 overflow-hidden rounded-xl border border-slate-200 bg-white text-sm shadow-lg dark:border-slate-600 dark:bg-slate-900"
                            role="listbox"
                            @mousedown.prevent
                        >
                            <template x-if="(state.q || '').trim().length === 0">
                                <div class="px-3 py-2.5 text-xs text-slate-500 dark:text-slate-400">
                                    {{ __('Digite para sugerir clientes, embarcações e inscrições.') }}
                                </div>
                            </template>
                            <template x-if="(state.q || '').trim().length > 0 && embBuscaMatches().length === 0">
                                <div class="px-3 py-2.5 text-xs text-slate-500 dark:text-slate-400">
                                    {{ __('Nenhuma sugestão.') }}
                                </div>
                            </template>
                            <ul class="max-h-64 overflow-y-auto py-1" x-show="embBuscaMatches().length > 0">
                                <template x-for="(item, idx) in embBuscaMatches()" :key="(item.kind || '') + '-' + (item.value || '') + '-' + idx">
                                    <li
                                        role="option"
                                        :aria-selected="embBuscaActive === idx"
                                        class="cursor-pointer px-3 py-2 text-left text-sm text-slate-800 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800"
                                        :class="{ 'bg-indigo-50 text-indigo-900 dark:bg-indigo-950/50 dark:text-indigo-100': embBuscaActive === idx }"
                                        @mouseenter="embBuscaActive = idx"
                                        @mousedown.prevent="pickEmbBusca(item)"
                                    >
                                        <span x-text="item.label"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        @if ($temAlgo)
                            <a
                                href="{{ route('embarcacoes.index') }}"
                                class="absolute inset-y-0 right-3 my-auto flex h-8 items-center rounded-full px-3 text-xs font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                @click.prevent="reset()"
                            >{{ __('Limpar') }}</a>
                        @endif
                    </div>
                </div>

                <div
                    class="min-h-[1px]"
                    x-html="html.tags"
                    @nx-embarcacoes-remove-filter.window="removeFilter($event.detail?.key)"
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
            Alpine.store('nxEmbarcacoes', { countText: null });

            window.nxEmbarcacoesIndex = ({ baseUrl, initial }) => ({
                state: {
                    q: initial.q ?? '',
                    per_page: initial.per_page ?? 5,
                    tipo: initial.tipo ?? '',
                    atividade: initial.atividade ?? '',
                    construtor: initial.construtor ?? '',
                    ano_construcao: initial.ano_construcao ?? '',
                    numero_motor: initial.numero_motor ?? '',
                },
                sugestoesBusca: Array.isArray(initial.sugestoesBusca) ? initial.sugestoesBusca : [],
                construtoresAll: Array.isArray(initial.construtores) ? initial.construtores : [],
                embBuscaOpen: false,
                embBuscaActive: -1,
                construtorEmbOpen: false,
                construtorEmbActive: -1,
                html: {
                    tags: @js(view('embarcacoes.partials.index-tags', [
                        'busca' => $busca ?? '',
                        'perPage' => $perPage ?? 5,
                        'tipo' => $tipo ?? '',
                        'atividade' => $atividade ?? '',
                        'construtor' => $construtor ?? '',
                        'anoConstrucao' => $anoConstrucao ?? '',
                        'numeroMotor' => $numeroMotor ?? '',
                    ])->render()),
                    list: @js(view('embarcacoes.partials.index-list', [
                        'embarcacoes' => $embarcacoes,
                        'busca' => $busca ?? '',
                        'tipo' => $tipo ?? '',
                        'atividade' => $atividade ?? '',
                        'construtor' => $construtor ?? '',
                        'anoConstrucao' => $anoConstrucao ?? '',
                        'numeroMotor' => $numeroMotor ?? '',
                    ])->render()),
                    pagination: @js($embarcacoes->hasPages() ? (string) $embarcacoes->links() : ''),
                },
                aborter: null,
                init() {
                    Alpine.store('nxEmbarcacoes').countText = @js(
                        ($busca ?? '') !== '' || ($tipo ?? '') !== '' || ($atividade ?? '') !== '' || ($construtor ?? '') !== '' || ($anoConstrucao ?? '') !== '' || ($numeroMotor ?? '') !== ''
                            ? trans_choice('{0} Nenhum resultado|{1} :count resultado encontrado|[2,*] :count resultados encontrados', (int) $embarcacoes->total(), ['count' => $embarcacoes->total()])
                            : trans_choice('{0} Nenhuma embarcação cadastrada|{1} :count cadastrada|[2,*] :count cadastradas', (int) $embarcacoes->total(), ['count' => $embarcacoes->total()])
                    );
                },
                filtrosEmbDrawerCount() {
                    let n = 0;
                    if ((this.state.tipo || '').trim() !== '') n++;
                    if ((this.state.atividade || '').trim() !== '') n++;
                    if ((this.state.construtor || '').trim() !== '') n++;
                    if ((this.state.ano_construcao || '').trim() !== '') n++;
                    if ((this.state.numero_motor || '').trim() !== '') n++;
                    return n;
                },
                embBuscaMatches() {
                    const raw = (this.state.q || '').trim();
                    if (raw.length < 1) return [];
                    const qLower = raw.toLowerCase();
                    const qDigits = raw.replace(/\D/g, '');
                    const out = [];
                    const all = this.sugestoesBusca;
                    for (let i = 0; i < all.length && out.length < 80; i++) {
                        const item = all[i];
                        const lab = String(item.label || '').toLowerCase();
                        const val = String(item.value || '').toLowerCase();
                        const labD = String(item.label || '').replace(/\D/g, '');
                        if (lab.includes(qLower) || val.includes(qLower) || (qDigits.length > 0 && labD.includes(qDigits))) {
                            out.push(item);
                        }
                    }
                    return out;
                },
                onEmbBuscaFocus() {
                    this.embBuscaOpen = true;
                    this.embBuscaActive = -1;
                },
                onEmbBuscaBlur() {
                    setTimeout(() => {
                        this.embBuscaOpen = false;
                        this.embBuscaActive = -1;
                        this.apply();
                    }, 180);
                },
                pickEmbBusca(item) {
                    this.state.q = item.value || '';
                    this.embBuscaOpen = false;
                    this.embBuscaActive = -1;
                    this.apply();
                },
                onEmbBuscaKeydown(e) {
                    const list = this.embBuscaMatches();
                    if (e.key === 'Escape') {
                        if (this.embBuscaOpen) {
                            e.preventDefault();
                            this.embBuscaOpen = false;
                            this.embBuscaActive = -1;
                        }
                        return;
                    }
                    if (!list.length || (this.state.q || '').trim().length < 1) return;
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (!this.embBuscaOpen) this.embBuscaOpen = true;
                        this.embBuscaActive = this.embBuscaActive < list.length - 1 ? this.embBuscaActive + 1 : this.embBuscaActive;
                        if (this.embBuscaActive < 0) this.embBuscaActive = 0;
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (!this.embBuscaOpen) this.embBuscaOpen = true;
                        this.embBuscaActive = this.embBuscaActive > 0 ? this.embBuscaActive - 1 : 0;
                    } else if (e.key === 'Enter' && this.embBuscaOpen && this.embBuscaActive >= 0 && list[this.embBuscaActive]) {
                        e.preventDefault();
                        this.pickEmbBusca(list[this.embBuscaActive]);
                    }
                },
                construtorEmbMatches() {
                    const q = (this.state.construtor || '').trim().toLowerCase();
                    if (q.length < 1) return [];
                    const out = [];
                    const all = this.construtoresAll;
                    for (let i = 0; i < all.length && out.length < 100; i++) {
                        const c = all[i];
                        if (String(c).toLowerCase().includes(q)) out.push(c);
                    }
                    return out;
                },
                onConstrutorEmbFocus() {
                    this.construtorEmbOpen = true;
                    this.construtorEmbActive = -1;
                },
                onConstrutorEmbBlur() {
                    setTimeout(() => {
                        this.construtorEmbOpen = false;
                        this.construtorEmbActive = -1;
                        this.apply();
                    }, 180);
                },
                pickConstrutorEmb(c) {
                    this.state.construtor = c;
                    this.construtorEmbOpen = false;
                    this.construtorEmbActive = -1;
                    this.apply();
                },
                onConstrutorEmbKeydown(e) {
                    const list = this.construtorEmbMatches();
                    if (e.key === 'Escape') {
                        if (this.construtorEmbOpen) {
                            e.preventDefault();
                            this.construtorEmbOpen = false;
                            this.construtorEmbActive = -1;
                        }
                        return;
                    }
                    if (!list.length || (this.state.construtor || '').trim().length < 1) return;
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (!this.construtorEmbOpen) this.construtorEmbOpen = true;
                        this.construtorEmbActive = this.construtorEmbActive < list.length - 1 ? this.construtorEmbActive + 1 : this.construtorEmbActive;
                        if (this.construtorEmbActive < 0) this.construtorEmbActive = 0;
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (!this.construtorEmbOpen) this.construtorEmbOpen = true;
                        this.construtorEmbActive = this.construtorEmbActive > 0 ? this.construtorEmbActive - 1 : 0;
                    } else if (e.key === 'Enter' && this.construtorEmbOpen && this.construtorEmbActive >= 0 && list[this.construtorEmbActive]) {
                        e.preventDefault();
                        this.pickConstrutorEmb(list[this.construtorEmbActive]);
                    }
                },
                removeFilter(key) {
                    if (key === 'tipo') this.state.tipo = '';
                    if (key === 'atividade') this.state.atividade = '';
                    if (key === 'construtor') this.state.construtor = '';
                    if (key === 'ano_construcao') this.state.ano_construcao = '';
                    if (key === 'numero_motor') this.state.numero_motor = '';
                    this.apply();
                },
                reset() {
                    this.state.q = '';
                    this.state.tipo = '';
                    this.state.atividade = '';
                    this.state.construtor = '';
                    this.state.ano_construcao = '';
                    this.state.numero_motor = '';
                    this.state.per_page = 5;
                    this.apply();
                },
                buildParams() {
                    const p = new URLSearchParams();
                    if (this.state.q) p.set('q', this.state.q);
                    if (this.state.tipo) p.set('tipo', this.state.tipo);
                    if (this.state.atividade) p.set('atividade', this.state.atividade);
                    if (this.state.construtor) p.set('construtor', this.state.construtor);
                    if (this.state.ano_construcao) p.set('ano_construcao', this.state.ano_construcao);
                    if (this.state.numero_motor) p.set('numero_motor', this.state.numero_motor);
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
                        Alpine.store('nxEmbarcacoes').countText = data.count_text ?? Alpine.store('nxEmbarcacoes').countText;
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
