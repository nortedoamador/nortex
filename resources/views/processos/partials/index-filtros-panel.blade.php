@php
    use App\Enums\TipoProcessoCategoria;
    use App\Models\Habilitacao;

    $fa = $filtrosAvancados ?? [];
    $tiposLista = ($tiposProcessoModal ?? collect())->sortBy('nome')->values();

    $nxServicosPorCat = collect(TipoProcessoCategoria::cases())
        ->mapWithKeys(function (TipoProcessoCategoria $c) use ($tiposLista): array {
            return [
                $c->value => $tiposLista
                    ->filter(fn ($t) => $t->categoria === $c)
                    ->map(fn ($t) => ['id' => $t->id, 'nome' => $t->nome])
                    ->values()
                    ->all(),
            ];
        })
        ->all();

    $nxClienteFiltroId = (int) ($fa['cliente'] ?? 0);
    $nxClientesSuggest = $clientesSuggestProcessoModal ?? collect();
    $nxClienteFiltroRow = ($nxClienteFiltroId > 0 && $nxClientesSuggest)
        ? collect($nxClientesSuggest)->firstWhere('id', $nxClienteFiltroId)
        : null;
    $nxClienteFiltroQ = is_array($nxClienteFiltroRow)
        ? (string) ($nxClienteFiltroRow['doc'] ?? $nxClienteFiltroRow['nome'] ?? '')
        : '';

    $nxClientePayloadId = 'nx-cliente-filtro-payload-'.bin2hex(random_bytes(8));
@endphp
<div
    x-data="{
        aberto: false,
        servicosPorCat: @js($nxServicosPorCat),
        categoriaSel: @js(($fa['cat'] ?? '') !== '' ? (string) $fa['cat'] : ''),
        tipoSel: @js(($fa['tipo'] ?? 0) > 0 ? (string) $fa['tipo'] : ''),
        servicosFiltrados() {
            return this.servicosPorCat[this.categoriaSel] || [];
        },
        init() {
            this.$watch('categoriaSel', () => {
                const list = this.servicosFiltrados();
                if (!list.some(s => String(s.id) === String(this.tipoSel))) this.tipoSel = '';
            });
        },
    }"
    class="contents"
    @keydown.escape.window="aberto = false"
>
    <button
        type="button"
        class="relative inline-flex h-9 items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-3.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/25 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-slate-500 dark:hover:bg-slate-800"
        @click="aberto = true"
        aria-haspopup="dialog"
        :aria-expanded="aberto"
    >
        <svg class="h-5 w-5 shrink-0 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
        </svg>
        {{ __('Filtros') }}
        @php $nxNAv = (int) ($fa['avancados_ativos'] ?? 0); @endphp
        <span
            id="nx-processos-filtros-badge"
            class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-indigo-600 px-1.5 text-[11px] font-bold leading-none text-white dark:bg-indigo-500 {{ $nxNAv > 0 ? '' : 'hidden' }}"
        >{{ $nxNAv }}</span>
    </button>

    <div
        x-show="aberto"
        x-cloak
        class="fixed inset-0 z-[60] flex justify-end"
        role="dialog"
        aria-modal="true"
        aria-labelledby="nx-processos-filtros-titulo"
    >
        <div
            class="absolute inset-0 bg-slate-900/40 backdrop-blur-[1px] dark:bg-black/50"
            @click="aberto = false"
            aria-hidden="true"
        ></div>
        <div
            x-show="aberto"
            x-transition:enter="transition transform ease-out duration-200"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition transform ease-in duration-150"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            class="relative flex h-full w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900"
            @click.stop
        >
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-4 dark:border-slate-700 sm:px-5">
                <h2 id="nx-processos-filtros-titulo" class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Filtros avançados') }}</h2>
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                    @click="aberto = false"
                >
                    <span class="sr-only">{{ __('Fechar') }}</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form id="nx-processos-advanced-form" method="GET" action="{{ route('processos.index') }}" class="flex min-h-0 flex-1 flex-col">
                <input type="hidden" name="v" value="{{ $visualizacao ?? 'list' }}" />
                @if (($busca ?? '') !== '')
                    <input type="hidden" name="q" value="{{ $busca }}" />
                @endif
                @if (filled($statusFiltro ?? null))
                    <input type="hidden" name="status" value="{{ $statusFiltro }}" />
                @endif

                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-4 py-5 sm:px-5">
                    <div>
                        <label for="nx_f_cat" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Tipo de serviço') }}</label>
                        <select
                            id="nx_f_cat"
                            name="cat"
                            x-model="categoriaSel"
                            class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                        >
                            <option value="">{{ __('Selecione o tipo de serviço…') }}</option>
                            @foreach (TipoProcessoCategoria::cases() as $c)
                                <option value="{{ $c->value }}">{{ $c->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="nx_f_tipo" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Tipo de processo') }}</label>
                        <select
                            id="nx_f_tipo"
                            name="tipo"
                            x-model="tipoSel"
                            class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                            :disabled="categoriaSel === ''"
                        >
                            <option value="">{{ __('Selecione o tipo de processo…') }}</option>
                            <template x-for="s in servicosFiltrados()" :key="s.id">
                                <option :value="String(s.id)" x-text="s.nome"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="nx_f_jurisdicao" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Jurisdição (Capitania / órgão)') }}</label>
                        <select
                            id="nx_f_jurisdicao"
                            name="jurisdicao"
                            class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                        >
                            <option value="">{{ __('Todas') }}</option>
                            @foreach (Habilitacao::JURISDICOES as $j)
                                <option value="{{ $j }}" @selected(($fa['jurisdicao'] ?? null) === $j)>{{ $j }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400" for="nx_f_cliente_q">{{ __('Cliente') }}</label>

                        <input type="hidden" id="nx_f_cliente" name="cliente" value="{{ $nxClienteFiltroId > 0 ? $nxClienteFiltroId : '' }}" />

                        {{-- Textarea (não <script>): JSON em <script> pode corromper o DOM; @json já escapa < --}}
                        <textarea
                            id="{{ $nxClientePayloadId }}"
                            class="hidden"
                            readonly
                            tabindex="-1"
                            aria-hidden="true"
                        >@json($nxClientesSuggest)</textarea>

                        <div
                            class="relative"
                            x-data="nxEmbarcacaoCpfSuggestEl('{{ $nxClientePayloadId }}', 'nx_f_cliente', '')"
                            data-nx-initial-q="{{ e($nxClienteFiltroQ) }}"
                        >
                            <input
                                type="text"
                                id="nx_f_cliente_q"
                                x-ref="cpfInput"
                                x-model="q"
                                autocomplete="off"
                                placeholder="{{ __('CPF ou nome do cliente') }}"
                                @input="filter()"
                                @focus="filter()"
                                @blur="onBlur()"
                                @keydown="onKeydown($event)"
                                class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                            />

                            <div
                                x-show="open"
                                x-cloak
                                x-bind:style="panelStyle"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-lg ring-1 ring-slate-900/5 dark:border-slate-700 dark:bg-slate-900 dark:ring-white/10"
                                style="display: none;"
                            >
                                <ul class="max-h-64 divide-y divide-slate-100 overflow-y-auto dark:divide-slate-800" role="listbox">
                                    <template x-for="(item, idx) in filtered" :key="String(item.id ?? '') + '|' + (item.doc || '') + '|' + idx">
                                        <li role="option" :aria-selected="idx === highlighted">
                                            <button
                                                type="button"
                                                class="flex w-full flex-col gap-0.5 px-3 py-2.5 text-left transition sm:flex-row sm:items-center sm:justify-between sm:gap-4"
                                                :class="idx === highlighted ? 'bg-indigo-50 dark:bg-indigo-950/50' : 'hover:bg-slate-50 dark:hover:bg-slate-800/80'"
                                                @mousedown.prevent="pick(item)"
                                            >
                                                <span class="shrink-0 font-mono text-sm font-semibold tracking-tight text-slate-900 dark:text-slate-100" x-text="item.doc"></span>
                                                <span class="min-w-0 truncate text-sm text-slate-600 dark:text-slate-400" x-text="item.nome"></span>
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                            </div>

                            <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Comece a digitar para sugerir clientes já cadastrados') }}</p>
                        </div>
                    </div>

                    <div>
                        <label for="nx_f_processo" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Nº do processo') }}</label>
                        <input
                            id="nx_f_processo"
                            type="number"
                            name="processo"
                            min="1"
                            step="1"
                            value="{{ ($fa['processo'] ?? 0) > 0 ? $fa['processo'] : '' }}"
                            placeholder="{{ __('Ex.: 42') }}"
                            @input.debounce.250ms="window.nxProcessosIndexApply && window.nxProcessosIndexApply()"
                            class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                        />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="nx_f_de" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Criado desde') }}</label>
                            <input
                                id="nx_f_de"
                                type="text"
                                inputmode="numeric"
                                maxlength="10"
                                autocomplete="off"
                                placeholder="dd/mm/aaaa"
                                data-nx-mask="date-br"
                                name="atualizado_de"
                                value="{{ filled($fa['atualizado_de'] ?? null) ? \Carbon\Carbon::parse((string) $fa['atualizado_de'])->format('d/m/Y') : '' }}"
                                @change="window.nxProcessosIndexApply && window.nxProcessosIndexApply()"
                                class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                            />
                        </div>
                        <div>
                            <label for="nx_f_ate" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Criado até') }}</label>
                            <input
                                id="nx_f_ate"
                                type="text"
                                inputmode="numeric"
                                maxlength="10"
                                autocomplete="off"
                                placeholder="dd/mm/aaaa"
                                data-nx-mask="date-br"
                                name="atualizado_ate"
                                value="{{ filled($fa['atualizado_ate'] ?? null) ? \Carbon\Carbon::parse((string) $fa['atualizado_ate'])->format('d/m/Y') : '' }}"
                                @change="window.nxProcessosIndexApply && window.nxProcessosIndexApply()"
                                class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                            />
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-600 dark:bg-slate-800/40">
                        <label class="flex cursor-pointer items-start gap-3">
                            <input
                                type="checkbox"
                                name="doc_pendente"
                                value="1"
                                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                                @checked(! empty($fa['doc_pendente']))
                            />
                            <span class="text-sm leading-snug text-slate-700 dark:text-slate-200">
                                <span class="font-semibold text-slate-900 dark:text-white">{{ __('Só com documento obrigatório pendente') }}</span>
                                <span class="mt-0.5 block text-xs font-normal text-slate-500 dark:text-slate-400">{{ __('Itens do checklist obrigatórios ainda em «Pendente» (conforme regras do tipo de processo).') }}</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="border-t border-slate-200 bg-slate-50/90 px-4 py-4 dark:border-slate-700 dark:bg-slate-800/50 sm:px-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                        <a
                            href="{{ route('processos.index', array_filter(['v' => $visualizacao ?? 'list', 'q' => filled($busca ?? '') ? $busca : null, 'status' => filled($statusFiltro ?? null) ? $statusFiltro : null], fn ($v) => $v !== null && $v !== '')) }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                            @click.prevent="
                                if (typeof window.nxProcessosClearFilters === 'function') {
                                    window.nxProcessosClearFilters();
                                }
                                window.nxProcessosIndexApply && window.nxProcessosIndexApply();
                                aberto = false;
                            "
                        >
                            {{ __('Limpar filtros avançados') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
