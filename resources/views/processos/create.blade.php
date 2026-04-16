@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\PlatformTipoProcesso> $tipos */
    /** @var \Illuminate\Support\Collection<int, array{id:int, hashid:string, doc:string, docDigits:string, nome:string}> $clientesSuggest */
    /** @var string $tiposExigenciasJson */
    /** @var string $servicosPorCategoriaJson */
    /** @var list<\App\Enums\TipoProcessoCategoria> $categoriasProcesso */
    /** @var string|null $categoriaProcessoOld */
    use App\Models\PlatformTipoProcesso;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Novo processo') }}</h2>
            </div>
            <a href="{{ route('processos.kanban') }}" class="shrink-0 text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('← Kanban') }}</a>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl">
            @if (session('status'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @if ($tipos->isEmpty())
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Nenhum tipo de processo disponível. Contacte o administrador.') }}</p>
                @else
                    <form
                        method="POST"
                        action="{{ route('processos.store') }}"
                        class="space-y-5"
                        x-data="{
                            tiposEx: {{ $tiposExigenciasJson }},
                            servicosPorCat: {{ $servicosPorCategoriaJson }},
                            categoriaSel: @js($categoriaProcessoOld ?? ''),
                            tipoSel: @js(old('platform_tipo_processo_id') !== null && old('platform_tipo_processo_id') !== '' ? (string) old('platform_tipo_processo_id') : ''),
                            lockCamposPresetEmbarcacao: false,
                            chaSlugsExigemHabilitacao: @js(PlatformTipoProcesso::SLUGS_EXIGEM_HABILITACAO_CHA_SELECIONADA),
                            servicosFiltrados() {
                                return this.servicosPorCat[this.categoriaSel] || [];
                            },
                            docsFiltrados() {
                                if (!this.tipoSel) return [];
                                const t = this.tiposEx.find(x => String(x.id) === String(this.tipoSel));
                                return t && t.documentos ? t.documentos : [];
                            },
                            tipoExigeHabilitacaoCha() {
                                const t = this.tiposEx.find(x => String(x.id) === String(this.tipoSel));
                                if (!t || t.categoria !== 'cha') return false;
                                return (this.chaSlugsExigemHabilitacao || []).includes(t.slug);
                            },
                            init() {
                                this.$watch('categoriaSel', () => {
                                    const list = this.servicosFiltrados();
                                    if (!list.some(s => String(s.id) === String(this.tipoSel))) this.tipoSel = '';
                                });
                            },
                        }"
                    >
                        @csrf
                        <div>
                            <x-input-label for="categoria_processo" value="{{ __('Tipo de serviço') }}" />
                            <select
                                id="categoria_processo"
                                x-model="categoriaSel"
                                class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
                                required
                            >
                                <option value="">{{ __('Selecione o tipo de serviço…') }}</option>
                                @foreach ($categoriasProcesso as $cat)
                                    <option value="{{ $cat->value }}" @selected($categoriaProcessoOld === $cat->value)>{{ $cat->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="tipo_processo_id" value="{{ __('Tipo de processo') }}" />
                            <select
                                id="tipo_processo_id"
                                name="platform_tipo_processo_id"
                                x-model="tipoSel"
                                class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
                                :required="categoriaSel !== ''"
                                :disabled="categoriaSel === ''"
                            >
                                <option value="">{{ __('Selecione o tipo de processo…') }}</option>
                                <template x-for="s in servicosFiltrados()" :key="s.id">
                                    <option :value="String(s.id)" x-text="s.nome"></option>
                                </template>
                            </select>
                            <x-input-error :messages="$errors->get('platform_tipo_processo_id')" class="mt-2" />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400" x-show="categoriaSel !== '' && servicosFiltrados().length === 0" x-cloak>
                                {{ __('Nenhum tipo de processo cadastrado nesta categoria.') }}
                            </p>
                        </div>

                        <div
                            x-show="tipoSel !== ''"
                            x-cloak
                            class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950/50"
                            style="display: none;"
                        >
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Documentos esperados no checklist') }}</p>
                            <ul class="mt-2 max-h-56 space-y-2 overflow-y-auto text-sm text-slate-700 dark:text-slate-300">
                                <template x-for="(d, idx) in docsFiltrados()" :key="idx">
                                    <li class="flex gap-2 border-b border-slate-200/80 pb-2 last:border-0 dark:border-slate-700/80">
                                        <span class="mt-0.5 shrink-0 text-slate-400" x-text="(idx + 1) + '.'"></span>
                                        <span>
                                            <span x-text="d.nome"></span>
                                            <span x-show="!d.obrigatorio" class="ml-1 text-xs font-semibold text-amber-700 dark:text-amber-400">({{ __('se aplicável') }})</span>
                                        </span>
                                    </li>
                                </template>
                            </ul>
                        </div>

                        @include('processos.partials.form-jurisdicao', ['idPrefix' => ''])

                        @include('processos.partials.form-cliente-cpf', [
                            'clientesSuggest' => $clientesSuggest,
                            'idPrefix' => '',
                        ])

                        <div
                            x-show="categoriaSel === 'embarcacao' && tipoSel !== ''"
                            x-cloak
                            class="space-y-2"
                            x-data="{
                                embarcacoes: [],
                                loading: false,
                                erro: '',
                                async loadEmbarcacoes() {
                                    this.embarcacoes = [];
                                    this.erro = '';
                                    const el = document.getElementById('cliente_id');
                                    const routeKey = el?.dataset?.clienteRouteKey || el?.dataset?.initialClienteRouteKey || '';
                                    if (!routeKey) return;
                                    this.loading = true;
                                    try {
                                        const res = await fetch(@js(route('clientes.embarcacoes.options', ['cliente' => '__NX_CLIENT_HASH__'])).replace('__NX_CLIENT_HASH__', routeKey), {
                                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                            credentials: 'same-origin',
                                        });
                                        if (!res.ok) throw new Error('HTTP ' + res.status);
                                        const data = await res.json();
                                        this.embarcacoes = Array.isArray(data.embarcacoes) ? data.embarcacoes : [];
                                    } catch (e) {
                                        this.erro = @js(__('Não foi possível carregar as embarcações do cliente.'));
                                    } finally {
                                        this.loading = false;
                                    }
                                },
                                init() {
                                    const el = document.getElementById('cliente_id');
                                    if (el) {
                                        this.loadEmbarcacoes();
                                        el.addEventListener('change', () => this.loadEmbarcacoes());
                                    }
                                },
                            }"
                        >
                            <x-input-label for="embarcacao_id" value="{{ __('Embarcação') }}" />
                            <select
                                id="embarcacao_id"
                                name="embarcacao_id"
                                class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
                                :required="categoriaSel === 'embarcacao' && tipoSel !== ''"
                            >
                                <option value="">{{ __('Selecione…') }}</option>
                                <template x-for="e in embarcacoes" :key="e.id">
                                    <option :value="String(e.id)" x-text="(e.nome || ('#' + e.id)) + (e.inscricao ? ' — ' + e.inscricao : '')"></option>
                                </template>
                            </select>
                            <x-input-error :messages="$errors->get('embarcacao_id')" class="mt-2" />
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-show="loading" x-cloak>{{ __('Carregando…') }}</p>
                            <p class="text-xs text-red-600 dark:text-red-400" x-show="erro !== ''" x-text="erro" x-cloak></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Obrigatório para processos de embarcação (usado para preencher modelos automaticamente).') }}</p>
                        </div>

                        <div
                            x-show="categoriaSel === 'cha' && tipoExigeHabilitacaoCha()"
                            x-cloak
                            class="space-y-2"
                            x-data="{
                                habilitacoes: [],
                                loading: false,
                                erro: '',
                                oldHabId: @json(old('habilitacao_id')),
                                async loadHabilitacoes() {
                                    this.habilitacoes = [];
                                    this.erro = '';
                                    const selHab = document.getElementById('habilitacao_id');
                                    const el = document.getElementById('cliente_id');
                                    const routeKey = el?.dataset?.clienteRouteKey || el?.dataset?.initialClienteRouteKey || '';
                                    if (!routeKey) {
                                        if (selHab) {
                                            selHab.value = '';
                                        }

                                        return;
                                    }
                                    this.loading = true;
                                    try {
                                        const res = await fetch(@js(route('clientes.habilitacoes.options', ['cliente' => '__NX_CLIENT_HASH__'])).replace('__NX_CLIENT_HASH__', routeKey), {
                                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                            credentials: 'same-origin',
                                        });
                                        if (!res.ok) throw new Error('HTTP ' + res.status);
                                        const data = await res.json();
                                        this.habilitacoes = Array.isArray(data.habilitacoes) ? data.habilitacoes : [];
                                    } catch (e) {
                                        this.erro = @js(__('Não foi possível carregar as habilitações do cliente.'));
                                    } finally {
                                        this.loading = false;
                                        this.$nextTick(() => {
                                            if (!selHab) {
                                                return;
                                            }
                                            const wanted = this.oldHabId != null && String(this.oldHabId) !== '' ? String(this.oldHabId) : '';
                                            if (wanted && this.habilitacoes.some((h) => String(h.id) === wanted)) {
                                                selHab.value = wanted;
                                            } else {
                                                selHab.value = '';
                                            }
                                        });
                                    }
                                },
                                init() {
                                    const el = document.getElementById('cliente_id');
                                    if (el) {
                                        this.loadHabilitacoes();
                                        el.addEventListener('change', () => this.loadHabilitacoes());
                                    }
                                },
                            }"
                        >
                            <x-input-label for="habilitacao_id" value="{{ __('CHA / Habilitação') }}" />
                            <select
                                id="habilitacao_id"
                                name="habilitacao_id"
                                class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
                                :required="categoriaSel === 'cha' && tipoExigeHabilitacaoCha()"
                            >
                                <option value="">{{ __('Selecione…') }}</option>
                                <template x-for="h in habilitacoes" :key="h.id">
                                    <option
                                        :value="String(h.id)"
                                        x-text="(h.categoria || '') + (h.numero_cha ? ' — CHA ' + h.numero_cha : '') + (h.data_validade ? ' — val. ' + h.data_validade : '')"
                                    ></option>
                                </template>
                            </select>
                            <x-input-error :messages="$errors->get('habilitacao_id')" class="mt-2" />
                            <p class="text-xs text-slate-500 dark:text-slate-400" x-show="loading" x-cloak>{{ __('Carregando…') }}</p>
                            <p class="text-xs text-red-600 dark:text-red-400" x-show="erro !== ''" x-text="erro" x-cloak></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Obrigatório para renovação, agregação de Motonauta e extravio da CHA.') }}</p>
                        </div>

                        <div>
                            <x-input-label for="create_observacoes" value="{{ __('Observações (opcional)') }}" />
                            <textarea
                                id="create_observacoes"
                                name="observacoes"
                                rows="3"
                                class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
                                placeholder="{{ __('Notas internas, prazos desejados, referências…') }}"
                            >{{ old('observacoes') }}</textarea>
                            <x-input-error :messages="$errors->get('observacoes')" class="mt-2" />
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('O processo inicia em «Em montagem». O checklist é gerado automaticamente conforme o tipo de processo escolhido.') }}
                        </p>

                        <div class="flex flex-wrap gap-3">
                            <x-primary-button>{{ __('Criar processo') }}</x-primary-button>
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                                {{ __('Cancelar') }}
                            </a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
