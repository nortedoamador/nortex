<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white sm:text-xl">{{ __('Clientes') }}</h2>
                    <span
                        class="inline-flex w-fit max-w-[200px] shrink-0 items-center justify-center truncate rounded-full border border-emerald-300/90 bg-brand-softer px-3 py-1.5 text-center text-xs font-semibold text-fg-brand-strong shadow-sm ring-1 ring-emerald-200/80 dark:border-emerald-700/80 dark:bg-brand-softer-dark dark:text-fg-brand-strong-dark dark:ring-emerald-800/80"
                        x-data
                        x-text="$store?.nxClientes?.countText ?? @js(($busca !== '' ? trans_choice('{0} Nenhum resultado|{1} :count resultado encontrado|[2,*] :count resultados encontrados', (int) $clientes->total(), ['count' => $clientes->total()]) : trans_choice('{0} Nenhum cliente cadastrado|{1} :count cadastrado|[2,*] :count cadastrados', (int) $clientes->total(), ['count' => $clientes->total()])))"
                    >
                        @if ($busca !== '')
                            {{ trans_choice('{0} Nenhum resultado|{1} :count resultado encontrado|[2,*] :count resultados encontrados', (int) $clientes->total(), ['count' => $clientes->total()]) }}
                        @else
                            {{ trans_choice('{0} Nenhum cliente cadastrado|{1} :count cadastrado|[2,*] :count cadastrados', (int) $clientes->total(), ['count' => $clientes->total()]) }}
                        @endif
                    </span>
                </div>
            </div>
            @can('create', App\Models\Cliente::class)
                <button
                    type="button"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:focus:ring-indigo-400/30"
                    @click="$store.novoCliente.open = true"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('Novo cliente') }}
                </button>
            @endcan
        </div>
    </x-slot>

    @can('create', App\Models\Cliente::class)
        <div
            class="hidden"
            x-data
            x-init="@if ($errors->any()) $store.novoCliente.open = true @endif"
        ></div>
        @include('clientes.partials.modal-novo-cliente', ['ufs' => $ufs])
    @endcan

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl space-y-6">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @cannot('create', App\Models\Cliente::class)
                <p class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400">
                    {{ __('Você pode visualizar clientes. Para cadastrar ou editar, é necessária a permissão «Gerir clientes».') }}
                </p>
            @endcannot

            <div
                x-data="nxClientesIndex({
                    baseUrl: @js(route('clientes.index')),
                    initial: {
                        q: @js($busca),
                        per_page: @js((int)($perPage ?? 5)),
                        tipo: @js(is_array($tipos ?? []) ? array_values($tipos) : []),
                        cidade: @js((string)($cidade ?? '')),
                        contato: @js((string)($contato ?? '')),
                        cidades: @js($cidadesOptions ?? []),
                        contatos: @js($contatosOptions ?? []),
                    },
                })"
                x-init="init()"
                class="space-y-3"
            >
            <form method="GET" action="{{ route('clientes.index') }}" class="space-y-3" @submit.prevent="apply()">
                @php
                    $hasAmbosTipos = is_array($tipos ?? []) && count(array_intersect($tipos, ['pf', 'pj'])) === 2;
                    $tipoEfetivo = $hasAmbosTipos ? null : ((is_array($tipos ?? []) && count($tipos) === 1) ? $tipos[0] : null);
                    $cidadeAtiva = is_string($cidade ?? null) ? trim((string) $cidade) : '';
                    $contatoAtivo = is_string($contato ?? null) ? trim((string) $contato) : '';
                    $nFiltros = 0;
                    if ($tipoEfetivo) { $nFiltros++; }
                    if ($cidadeAtiva !== '') { $nFiltros++; }
                    if ($contatoAtivo !== '') { $nFiltros++; }
                    $temAlgo = ($busca ?? '') !== '' || $nFiltros > 0 || ((int)($perPage ?? 5)) !== 5;
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

                        @include('clientes.partials.index-filtros-drawer')
                    </div>

                    <div class="relative flex-1">
                        <label class="sr-only" for="busca_clientes">{{ __('Buscar clientes') }}</label>
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400 dark:text-slate-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </span>
                        <input
                            id="busca_clientes"
                            type="search"
                            name="q"
                            x-model="state.q"
                            @input.debounce.250ms="apply()"
                            placeholder="{{ __('Buscar por nome ou CPF…') }}"
                            autocomplete="off"
                            class="w-full rounded-full border border-slate-200 bg-white py-3 pl-12 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500 {{ $temAlgo ? 'pr-24' : 'pr-4' }}"
                        />
                        @if ($temAlgo)
                            <a
                                href="{{ route('clientes.index') }}"
                                class="absolute inset-y-0 right-3 my-auto flex h-8 items-center rounded-full px-3 text-xs font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                @click.prevent="reset()"
                            >{{ __('Limpar') }}</a>
                        @endif
                    </div>
                </div>

                <div
                    class="min-h-[1px]"
                    x-html="html.tags"
                    @nx-clientes-remove-filter.window="removeFilter($event.detail?.key)"
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
</x-app-layout>

<script>
    (function () {
        const register = () => {
            Alpine.store('nxClientes', { countText: null });

            window.nxClientesIndex = ({ baseUrl, initial }) => ({
            state: {
                q: initial.q ?? '',
                per_page: initial.per_page ?? 5,
                tipo: Array.isArray(initial.tipo) ? initial.tipo : [],
                cidade: initial.cidade ?? '',
                contato: initial.contato ?? '',
            },
            cidadesAll: Array.isArray(initial.cidades) ? initial.cidades : [],
            cidadeFiltroOpen: false,
            cidadeFiltroActive: -1,
            contatosAll: Array.isArray(initial.contatos) ? initial.contatos : [],
            contatoFiltroOpen: false,
            contatoFiltroActive: -1,
            html: {
                tags: @js(view('clientes.partials.index-tags', ['busca' => $busca, 'perPage' => $perPage, 'tipos' => $tipos, 'cidade' => $cidade ?? '', 'contato' => $contato ?? ''])->render()),
                list: @js(view('clientes.partials.index-list', ['clientes' => $clientes, 'busca' => $busca])->render()),
                pagination: @js($clientes->hasPages() ? (string) $clientes->links() : ''),
            },
            aborter: null,
            init() {
                Alpine.store('nxClientes').countText = @js(($busca !== '' ? trans_choice('{0} Nenhum resultado|{1} :count resultado encontrado|[2,*] :count resultados encontrados', (int) $clientes->total(), ['count' => $clientes->total()]) : trans_choice('{0} Nenhum cliente cadastrado|{1} :count cadastrado|[2,*] :count cadastrados', (int) $clientes->total(), ['count' => $clientes->total()])));
            },
            toggleTipo(v) {
                const i = this.state.tipo.indexOf(v);
                if (i >= 0) this.state.tipo.splice(i, 1);
                else this.state.tipo.push(v);
                this.apply();
            },
            filtrosAtivosCount() {
                const t = this.state.tipo || [];
                const hasAmbos = t.includes('pf') && t.includes('pj');
                const tipoEfetivo = hasAmbos || t.length === 0 ? null : (t.length === 1 ? t[0] : null);
                let n = 0;
                if (tipoEfetivo) n++;
                if ((this.state.cidade || '').trim() !== '') n++;
                if ((this.state.contato || '').trim() !== '') n++;
                return n;
            },
            removeFilter(key) {
                if (key === 'tipo') this.state.tipo = [];
                if (key === 'cidade') this.state.cidade = '';
                if (key === 'contato') this.state.contato = '';
                this.apply();
            },
            cidadesFiltroMatches() {
                const q = (this.state.cidade || '').trim().toLowerCase();
                if (q.length < 1) return [];
                const out = [];
                const all = this.cidadesAll;
                for (let i = 0; i < all.length && out.length < 100; i++) {
                    const c = all[i];
                    if (String(c).toLowerCase().includes(q)) out.push(c);
                }
                return out;
            },
            onCidadeFiltroFocus() {
                this.cidadeFiltroOpen = true;
                this.cidadeFiltroActive = -1;
            },
            onCidadeFiltroBlur() {
                setTimeout(() => {
                    this.cidadeFiltroOpen = false;
                    this.cidadeFiltroActive = -1;
                    this.apply();
                }, 180);
            },
            pickCidadeFiltro(c) {
                this.state.cidade = c;
                this.cidadeFiltroOpen = false;
                this.cidadeFiltroActive = -1;
                this.apply();
            },
            onCidadeFiltroKeydown(e) {
                const list = this.cidadesFiltroMatches();
                if (e.key === 'Escape') {
                    if (this.cidadeFiltroOpen) {
                        e.preventDefault();
                        this.cidadeFiltroOpen = false;
                        this.cidadeFiltroActive = -1;
                    }
                    return;
                }
                if (!list.length || (this.state.cidade || '').trim().length < 1) return;
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!this.cidadeFiltroOpen) this.cidadeFiltroOpen = true;
                    this.cidadeFiltroActive = this.cidadeFiltroActive < list.length - 1
                        ? this.cidadeFiltroActive + 1
                        : this.cidadeFiltroActive;
                    if (this.cidadeFiltroActive < 0) this.cidadeFiltroActive = 0;
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (!this.cidadeFiltroOpen) this.cidadeFiltroOpen = true;
                    this.cidadeFiltroActive = this.cidadeFiltroActive > 0 ? this.cidadeFiltroActive - 1 : 0;
                } else if (e.key === 'Enter' && this.cidadeFiltroOpen && this.cidadeFiltroActive >= 0 && list[this.cidadeFiltroActive]) {
                    e.preventDefault();
                    this.pickCidadeFiltro(list[this.cidadeFiltroActive]);
                }
            },
            contatosFiltroMatches() {
                const rawQ = (this.state.contato || '').trim();
                if (rawQ.length < 1) return [];
                const qLower = rawQ.toLowerCase();
                const qDigits = rawQ.replace(/\D/g, '');
                const out = [];
                const all = this.contatosAll;
                for (let i = 0; i < all.length && out.length < 100; i++) {
                    const c = String(all[i]);
                    const cLower = c.toLowerCase();
                    const cDigits = c.replace(/\D/g, '');
                    if (cLower.includes(qLower) || (qDigits.length > 0 && cDigits.includes(qDigits))) {
                        out.push(c);
                    }
                }
                return out;
            },
            onContatoFiltroFocus() {
                this.contatoFiltroOpen = true;
                this.contatoFiltroActive = -1;
            },
            onContatoFiltroBlur() {
                setTimeout(() => {
                    this.contatoFiltroOpen = false;
                    this.contatoFiltroActive = -1;
                    this.apply();
                }, 180);
            },
            pickContatoFiltro(v) {
                this.state.contato = v;
                this.contatoFiltroOpen = false;
                this.contatoFiltroActive = -1;
                this.apply();
            },
            onContatoFiltroKeydown(e) {
                const list = this.contatosFiltroMatches();
                if (e.key === 'Escape') {
                    if (this.contatoFiltroOpen) {
                        e.preventDefault();
                        this.contatoFiltroOpen = false;
                        this.contatoFiltroActive = -1;
                    }
                    return;
                }
                if (!list.length || (this.state.contato || '').trim().length < 1) return;
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!this.contatoFiltroOpen) this.contatoFiltroOpen = true;
                    this.contatoFiltroActive = this.contatoFiltroActive < list.length - 1
                        ? this.contatoFiltroActive + 1
                        : this.contatoFiltroActive;
                    if (this.contatoFiltroActive < 0) this.contatoFiltroActive = 0;
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (!this.contatoFiltroOpen) this.contatoFiltroOpen = true;
                    this.contatoFiltroActive = this.contatoFiltroActive > 0 ? this.contatoFiltroActive - 1 : 0;
                } else if (e.key === 'Enter' && this.contatoFiltroOpen && this.contatoFiltroActive >= 0 && list[this.contatoFiltroActive]) {
                    e.preventDefault();
                    this.pickContatoFiltro(list[this.contatoFiltroActive]);
                }
            },
            reset() {
                this.state.q = '';
                this.state.tipo = [];
                this.state.cidade = '';
                this.state.contato = '';
                this.state.per_page = 5;
                this.apply();
            },
            buildParams() {
                const p = new URLSearchParams();
                if (this.state.q) p.set('q', this.state.q);
                if (this.state.cidade) p.set('cidade', this.state.cidade);
                if (this.state.contato) p.set('contato', this.state.contato);
                if (this.state.per_page && Number(this.state.per_page) !== 5) p.set('per_page', String(this.state.per_page));
                if (Array.isArray(this.state.tipo)) {
                    this.state.tipo.forEach(t => p.append('tipo[]', t));
                }
                return p;
            },
            async apply(url = null) {
                const params = this.buildParams();
                const target = url ? new URL(url) : new URL(baseUrl);
                // Preserva pagina quando clicado; caso contrário, volta pra 1 removendo page
                if (!url) target.searchParams.delete('page');
                params.forEach((v, k) => target.searchParams.append(k, v));

                history.replaceState({}, '', target.toString());

                try {
                    if (this.aborter) this.aborter.abort();
                    this.aborter = new AbortController();

                    const res = await fetch(target.toString(), {
                        headers: { 'Accept': 'application/json' },
                        signal: this.aborter.signal,
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    Alpine.store('nxClientes').countText = data.count_text ?? Alpine.store('nxClientes').countText;
                    this.html.tags = data.tags_html ?? this.html.tags;
                    this.html.list = data.list_html ?? this.html.list;
                    this.html.pagination = data.pagination_html ?? '';
                } catch (e) {
                    // ignore abort/network
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
