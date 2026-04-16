{{-- Modal: $tipos (com documentoRegras), $clientesSuggest, $categoriasProcesso, $categoriaProcessoOld --}}
@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\PlatformTipoProcesso> $tipos */
    /** @var \Illuminate\Support\Collection<int, array{id:int, hashid:string, doc:string, docDigits:string, nome:string}>|iterable $clientesSuggest */
    /** @var list<\App\Enums\TipoProcessoCategoria> $categoriasProcesso */
    /** @var string|null $categoriaProcessoOld */
    use App\Enums\TipoProcessoCategoria;
    use App\Models\PlatformTipoProcesso;

    $nxNpTipos = $tipos->map(fn (\App\Models\PlatformTipoProcesso $t) => [
        'id' => $t->id,
        'nome' => $t->nome,
        'slug' => $t->slug,
        'categoria' => $t->categoria instanceof TipoProcessoCategoria ? $t->categoria->value : (string) $t->categoria,
        'documentos' => $t->documentoRegras->map(fn ($d) => [
            'nome' => $d->nome,
            'obrigatorio' => (bool) $d->pivot->obrigatorio,
        ])->values(),
    ])->values();

    $nxNpServicos = collect(TipoProcessoCategoria::cases())
        ->mapWithKeys(function (TipoProcessoCategoria $c) use ($tipos): array {
            return [
                $c->value => $tipos
                    ->filter(fn (\App\Models\PlatformTipoProcesso $t) => $t->categoria === $c)
                    ->sortBy(fn (\App\Models\PlatformTipoProcesso $t) => [(int) ($t->ordem ?? 0), (string) ($t->nome ?? '')])
                    ->map(fn (\App\Models\PlatformTipoProcesso $t) => ['id' => $t->id, 'nome' => $t->nome])
                    ->values()
                    ->all(),
            ];
        })
        ->all();

    $nxNpLabels = collect($categoriasProcesso)->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();

    $nxNovoProcessoModalConfig = [
        'base' => url('/processos'),
        'store' => route('processos.store'),
        'categoriaSel' => $categoriaProcessoOld ?? '',
        'tipoSel' => old('platform_tipo_processo_id') !== null && old('platform_tipo_processo_id') !== '' ? (string) old('platform_tipo_processo_id') : '',
        'passoInicial' => old('_novo_processo_passo') === 'detalhes' ? 2 : 1,
        'skipResetOnFirstOpen' => $errors->any(),
        'chaSlugsExigemHabilitacao' => PlatformTipoProcesso::SLUGS_EXIGEM_HABILITACAO_CHA_SELECIONADA,
        'msgs' => [
            'selTipoProcesso' => __('Selecione o tipo de processo.'),
            'selTipoServico' => __('Selecione o tipo de serviço.'),
            'selJurisdicao' => __('Selecione a jurisdição (capitania / órgão).'),
            'selHabilitacaoCha' => __('Selecione a CHA (habilitação) do cliente para este serviço.'),
            'selClienteLista' => __('Selecione o cliente pela identificação (lista de sugestões).'),
            'informeIdentificacao' => __('Informe a identificação do cliente.'),
            'nomeCompleto' => __('O nome completo deve ser preenchido ao escolher o cliente.'),
            'erroCriar' => __('Não foi possível criar o processo.'),
            'erroRede' => __('Falha de rede. Tente novamente.'),
            'selecioneArquivos' => __('Selecione ao menos um arquivo.'),
            'erroUpload' => __('Não foi possível enviar os arquivos.'),
            'erroRemoverAnexo' => __('Não foi possível remover o anexo.'),
            'erroAtualizarDoc' => __('Não foi possível atualizar o documento.'),
            'erroSalvar' => __('Não foi possível salvar.'),
            'trocarAnexo' => [
                'titulo' => __('Trocar anexo?'),
                'frase' => __('O ficheiro enviado anteriormente será eliminado ao substituir por um novo anexo.'),
                'pergunta' => __('Deseja realmente trocar o anexo?'),
                'confirmar' => __('Sim, trocar'),
                'cancelar' => __('Não, cancelar'),
            ],
        ],
    ];
@endphp

<div
    x-show="$store.novoProcesso.open"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/70 px-4 py-6 sm:py-10"
    style="display: none;"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-novo-processo-titulo"
>
    <div
        class="flex max-h-[min(92vh,920px)] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900"
        @click.stop
        x-data="nxNovoProcessoModal({!! \Illuminate\Support\Js::from($nxNovoProcessoModalConfig) !!})"
    >
        {{-- Payload JSON: fora do x-data para aceitar apóstrofos em textos --}}
        <script type="application/json" id="nx-np-json-tipos" class="hidden">@json($nxNpTipos)</script>
        <script type="application/json" id="nx-np-json-servicos" class="hidden">@json($nxNpServicos)</script>
        <script type="application/json" id="nx-np-json-labels" class="hidden">@json($nxNpLabels)</script>

        <div class="flex shrink-0 items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-slate-700 sm:px-6">
            <div>
                <div x-show="passo === 1" x-cloak>
                    <h2 id="modal-novo-processo-titulo" class="text-lg font-semibold text-slate-900 dark:text-white">
                        {{ __('Novo processo') }}
                    </h2>
                </div>
                <div x-show="passo === 2" x-cloak style="display: none;">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                        {{ __('Detalhes do processo') }}
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Confira e complemente antes de criar') }}</p>
                </div>
            </div>
            <button
                type="button"
                class="rounded-lg p-1.5 text-red-600 transition hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-950/50 dark:hover:text-red-300"
                @click="$store.novoProcesso.open = false"
                aria-label="{{ __('Fechar') }}"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4 sm:px-6 sm:py-5">
            @if ($tipos->isEmpty())
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Nenhum tipo de processo disponível.') }}</p>
            @else
                <form id="nx-modal-novo-processo-form" method="POST" action="{{ route('processos.store') }}" class="space-y-4" novalidate>
                    @csrf
                    <input type="hidden" name="_novo_processo_passo" :value="passo === 2 ? 'detalhes' : 'inicio'" />

                    <div x-show="passo === 1" class="space-y-4" x-cloak>
                        <div>
                            <x-input-label for="modal_categoria_processo" value="{{ __('Tipo de serviço') }}" />
                            <select
                                id="modal_categoria_processo"
                                x-model="categoriaSel"
                                :disabled="lockCamposPresetEmbarcacao"
                                class="mt-1 block w-full rounded-xl border text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
                                :class="lockCamposPresetEmbarcacao
                                    ? 'cursor-not-allowed border-slate-200 bg-slate-200 text-slate-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300'
                                    : 'border-slate-300'"
                            >
                                <option value="">{{ __('Selecione o tipo de serviço…') }}</option>
                                @foreach ($categoriasProcesso as $cat)
                                    <option value="{{ $cat->value }}" @selected($categoriaProcessoOld === $cat->value)>{{ $cat->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="modal_tipo_processo_id" value="{{ __('Tipo de processo') }}" />
                            <select
                                id="modal_tipo_processo_id"
                                name="platform_tipo_processo_id"
                                x-model="tipoSel"
                                class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
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
                            <ul class="mt-2 max-h-48 space-y-2 overflow-y-auto text-sm text-slate-700 dark:text-slate-300">
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

                        @include('processos.partials.form-jurisdicao', [
                            'idPrefix' => 'modal_proc_',
                            'required' => false,
                        ])

                        @include('processos.partials.form-cliente-cpf', [
                            'clientesSuggest' => $clientesSuggest,
                            'idPrefix' => 'modal_proc_',
                            'htmlRequired' => false,
                        ])

                        <template x-if="lockCamposPresetEmbarcacao && presetEmbarcacaoIdLocked">
                            <input type="hidden" name="embarcacao_id" :value="presetEmbarcacaoIdLocked" />
                        </template>

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
                                    const el = document.getElementById('modal_proc_cliente_id');
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
                                    const el = document.getElementById('modal_proc_cliente_id');
                                    if (el) {
                                        this.loadEmbarcacoes();
                                        el.addEventListener('change', () => this.loadEmbarcacoes());
                                    }
                                },
                            }"
                        >
                            <x-input-label for="modal_proc_embarcacao_id" value="{{ __('Embarcação') }}" />
                            <select
                                id="modal_proc_embarcacao_id"
                                x-bind:name="lockCamposPresetEmbarcacao ? null : 'embarcacao_id'"
                                :disabled="lockCamposPresetEmbarcacao"
                                class="mt-1 block w-full rounded-xl border text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
                                :class="lockCamposPresetEmbarcacao
                                    ? 'cursor-not-allowed border-slate-200 bg-slate-200 text-slate-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300'
                                    : 'border-slate-300'"
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
                                async loadHabilitacoes() {
                                    this.habilitacoes = [];
                                    this.erro = '';
                                    const selHab = document.getElementById('modal_proc_habilitacao_id');
                                    if (selHab) {
                                        selHab.value = '';
                                    }
                                    const el = document.getElementById('modal_proc_cliente_id');
                                    const routeKey = el?.dataset?.clienteRouteKey || el?.dataset?.initialClienteRouteKey || '';
                                    if (!routeKey) return;
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
                                    }
                                },
                                init() {
                                    const el = document.getElementById('modal_proc_cliente_id');
                                    if (el) {
                                        this.loadHabilitacoes();
                                        el.addEventListener('change', () => this.loadHabilitacoes());
                                    }
                                },
                            }"
                        >
                            <x-input-label for="modal_proc_habilitacao_id" value="{{ __('CHA / Habilitação') }}" />
                            <select
                                id="modal_proc_habilitacao_id"
                                name="habilitacao_id"
                                class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
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

                        <div
                            x-show="erroPasso1 !== ''"
                            x-cloak
                            class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200"
                            role="alert"
                        >
                            <span x-text="erroPasso1"></span>
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Ao prosseguir, o processo é criado em «Em montagem»; na próxima etapa você anexa documentos e confirma.') }}
                        </p>

                        <div class="sticky bottom-0 flex flex-col gap-2 border-t border-slate-200 bg-white pt-4 dark:border-slate-700 dark:bg-slate-900 sm:flex-row sm:justify-end">
                            <button
                                type="button"
                                class="w-full rounded-xl border border-slate-300 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800 sm:w-auto sm:px-5"
                                @click="$store.novoProcesso.open = false"
                            >
                                {{ __('Desistir') }}
                            </button>
                            <button
                                type="button"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-slate-900 sm:w-auto sm:px-6"
                                :disabled="enviandoPasso1"
                                @click="avancar()"
                            >
                                <span x-show="!enviandoPasso1">{{ __('Proseguir') }}</span>
                                <span x-show="enviandoPasso1" x-cloak>{{ __('Criando…') }}</span>
                            </button>
                        </div>
                    </div>

                    <div x-show="passo === 2" x-cloak class="space-y-4" style="display: none;">
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            {{ __('O processo já foi criado. Envie os documentos pendentes, ajuste as observações e conclua para abrir a página do processo.') }}
                        </p>

                        <dl class="grid grid-cols-1 gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm sm:grid-cols-2 sm:gap-x-6 sm:gap-y-3 dark:border-slate-700 dark:bg-slate-950/50">
                            <div class="flex min-w-0 flex-col gap-0.5">
                                <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('Tipo de serviço') }}</dt>
                                <dd class="text-slate-900 dark:text-slate-100" x-text="nomeTipoServico()"></dd>
                            </div>
                            <div class="flex min-w-0 flex-col gap-0.5">
                                <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('Tipo de processo') }}</dt>
                                <dd class="text-slate-900 dark:text-slate-100" x-text="nomeTipoProcesso()"></dd>
                            </div>
                            <div class="flex min-w-0 flex-col gap-0.5 sm:col-span-2">
                                <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('Jurisdição (Capitania / órgão)') }}</dt>
                                <dd class="break-words text-slate-900 dark:text-slate-100" x-text="nomeJurisdicaoResumo()"></dd>
                            </div>
                            <div class="flex min-w-0 flex-col gap-0.5">
                                <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('Nome do cliente') }}</dt>
                                <dd class="break-words text-slate-900 dark:text-slate-100" x-text="nomeClienteResumo()"></dd>
                            </div>
                            <div class="flex min-w-0 flex-col gap-0.5">
                                <dt class="font-medium text-slate-500 dark:text-slate-400">{{ __('Identificação (CPF)') }}</dt>
                                <dd class="break-all font-mono text-slate-900 dark:text-slate-100" x-text="cpfClienteResumo()"></dd>
                            </div>
                        </dl>

                        <div>
                            <div class="mb-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                                <p class="shrink-0 text-xs font-bold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Checklist de documentos') }}</p>
                                <div class="flex min-w-0 flex-1 items-center gap-2">
                                    <div class="relative h-5 min-h-[1.25rem] flex-1 overflow-hidden rounded-sm bg-slate-200 dark:bg-slate-700">
                                        <div
                                            class="absolute inset-y-0 left-0 rounded-sm transition-[width] duration-300 ease-out"
                                            :class="(progresso.percentual >= 100 || (Number(progresso.total_itens_ativos ?? progresso.obrigatorios_ativos ?? 0) === 0)) ? 'bg-emerald-500' : 'bg-amber-500'"
                                            :style="'width: ' + Math.min(100, Math.round(Number(progresso.percentual) || 0)) + '%'"
                                        ></div>
                                        <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                            <span
                                                class="text-[10px] font-bold tabular-nums leading-none"
                                                :class="Math.min(100, Math.round(Number(progresso.percentual) || 0)) >= 45 ? 'text-white drop-shadow-[0_1px_1px_rgba(0,0,0,0.25)]' : 'text-slate-700 dark:text-slate-200'"
                                                x-text="Math.round(Number(progresso.percentual) || 0) + '%'"
                                            ></span>
                                        </div>
                                    </div>
                                    <span class="shrink-0 tabular-nums text-xs text-slate-600 dark:text-slate-400" x-text="(progresso.enviados ?? 0) + '/' + (progresso.total_itens_ativos ?? progresso.obrigatorios_ativos ?? 0)"></span>
                                </div>
                            </div>
                            <ul class="mt-2 divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white dark:divide-slate-800 dark:border-slate-700 dark:bg-slate-950/40">
                                <template x-for="(row, idx) in checklistDocs" :key="row.id">
                                    <li class="px-3 py-3 text-sm sm:px-4">
                                        <div class="flex items-start gap-3">
                                            <span class="relative mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center" :title="row.status">
                                                <svg x-show="row.status === 'enviado' || row.status === 'fisico'" class="absolute h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                                <svg x-show="row.status === 'dispensado'" x-cloak class="absolute h-6 w-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />
                                                </svg>
                                                <svg x-show="row.status === 'pendente'" x-cloak class="absolute h-6 w-6 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-slate-800 dark:text-slate-200">
                                                    <span x-text="row.nome"></span>
                                                    <span x-show="!row.obrigatorio" class="ml-1 text-xs font-semibold text-amber-700 dark:text-amber-400">({{ __('se aplicável') }})</span>
                                                </p>
                                                <p x-show="row.status === 'fisico'" class="mt-1 text-[11px] font-medium text-slate-600 dark:text-slate-400" x-cloak>{{ __('Entrega física do documento (sem arquivo digital).') }}</p>
                                                <div x-show="row.codigo === 'CHA_CNH_COM_VALIDADE'" class="mt-2 flex flex-wrap items-center gap-2" x-cloak>
                                                    <label class="text-[11px] font-medium text-slate-600 dark:text-slate-400" :for="'nx-np-validade-' + row.id">{{ __('Validade da CNH') }}</label>
                                                    <input
                                                        type="text"
                                                        class="rounded-md border border-slate-300 bg-white px-2 py-1 text-xs text-slate-800 shadow-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                                                        :id="'nx-np-validade-' + row.id"
                                                        :value="(row.data_validade_documento && String(row.data_validade_documento).match(/^\\d{4}-\\d{2}-\\d{2}$/)) ? (String(row.data_validade_documento).slice(8,10) + '/' + String(row.data_validade_documento).slice(5,7) + '/' + String(row.data_validade_documento).slice(0,4)) : (row.data_validade_documento || '')"
                                                        inputmode="numeric"
                                                        maxlength="10"
                                                        autocomplete="off"
                                                        placeholder="dd/mm/aaaa"
                                                        data-nx-mask="date-br"
                                                        :disabled="enviandoAnexosDocId !== null || removendoAnexoId !== null || atualizandoDocStatusId !== null || !processoId"
                                                        @change="salvarValidadeCnh(row, $event.target.value)"
                                                    />
                                                </div>
                                                <p x-show="row.codigo === 'CHA_ATESTADO_MEDICO_PSICOFISICO' && row.status === 'dispensado'" class="mt-1 text-[11px] text-slate-600 dark:text-slate-400" x-cloak>{{ __('Dispensado automaticamente: CNH válida anexada com data de validade em vigor.') }}</p>
                                                <div class="mt-1.5 flex flex-wrap gap-1.5" x-show="row.anexos && row.anexos.length" x-cloak>
                                                    <template x-for="anexo in row.anexos" :key="anexo.id">
                                                        <span class="inline-flex max-w-full items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200">
                                                            <a
                                                                :href="urlAbsoluta(anexo.url)"
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                class="truncate hover:underline"
                                                                x-text="anexo.nome_original"
                                                            ></a>
                                                            <button
                                                                type="button"
                                                                class="shrink-0 rounded p-0.5 text-emerald-700 hover:bg-emerald-100 dark:text-emerald-300 dark:hover:bg-emerald-900/40"
                                                                :disabled="removendoAnexoId !== null || enviandoAnexosDocId !== null || atualizandoDocStatusId !== null"
                                                                aria-label="{{ __('Remover anexo') }}"
                                                                @click="removerAnexo(row.id, anexo.id)"
                                                            >
                                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                                            </button>
                                                        </span>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="flex shrink-0 flex-col items-end gap-1.5 pt-0.5">
                                                <input
                                                    type="file"
                                                    :id="'nx-np-file-' + row.id"
                                                    multiple
                                                    class="hidden"
                                                    :accept="permiteVariosAnexosFotos(row) ? '.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp' : '.pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,application/pdf,image/*'"
                                                    @change="enviarAnexosParaDocumento(row.id)"
                                                />
                                                <div class="flex flex-wrap justify-end gap-1.5">
                                                    <a
                                                        x-show="row.status === 'pendente' && row.url_abrir_modelo"
                                                        x-cloak
                                                        :href="urlAbsoluta(row.url_abrir_modelo)"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-indigo-200 bg-indigo-50 text-indigo-900 shadow-sm hover:bg-indigo-100 dark:border-indigo-900/40 dark:bg-indigo-950/40 dark:text-indigo-200 dark:hover:bg-indigo-900/30"
                                                        title="{{ __('Abrir modelo') }}"
                                                        aria-label="{{ __('Abrir modelo') }}"
                                                    >
                                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                    </a>
                                                    <button
                                                        type="button"
                                                        x-show="row.status === 'pendente' && (row.codigo === 'COMPROVANTE_RESIDENCIA_CEP' || row.modelo_slug === 'anexo-2g')"
                                                        x-cloak
                                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-sky-200 bg-sky-50 text-sky-900 shadow-sm hover:bg-sky-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-sky-900/40 dark:bg-sky-950/40 dark:text-sky-200 dark:hover:bg-sky-900/30"
                                                        title="{{ __('Registrar declaração de residência (Anexo 2-G)') }}"
                                                        aria-label="{{ __('Registrar declaração de residência (Anexo 2-G)') }}"
                                                        :disabled="enviandoAnexosDocId !== null || removendoAnexoId !== null || atualizandoDocStatusId !== null || !processoId"
                                                        @click="patchDocumentoStatus(row.id, 'enviado', { declaracao_residencia_2g: true })"
                                                    >
                                                        <svg x-show="atualizandoDocStatusId != row.id" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                                        <svg x-show="atualizandoDocStatusId == row.id" x-cloak class="h-4 w-4 animate-spin text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        x-show="row.status === 'pendente' && (row.codigo === 'CHA_REQ_ANEXO_5H' || row.codigo === 'CHA_REQ_ANEXO_5H_OCORRENCIA' || row.modelo_slug === 'anexo-5h')"
                                                        x-cloak
                                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-violet-200 bg-violet-50 text-violet-900 shadow-sm hover:bg-violet-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-violet-900/40 dark:bg-violet-950/40 dark:text-violet-200 dark:hover:bg-violet-900/30"
                                                        title="{{ __('Preencher requerimento com o modelo PDF (NORMAM 211)') }}"
                                                        aria-label="{{ __('Preencher requerimento com o modelo PDF (NORMAM 211)') }}"
                                                        :disabled="enviandoAnexosDocId !== null || removendoAnexoId !== null || atualizandoDocStatusId !== null || !processoId"
                                                        @click="patchDocumentoStatus(row.id, 'enviado', { declaracao_anexo_5h: true })"
                                                    >
                                                        <svg x-show="atualizandoDocStatusId != row.id" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                                        <svg x-show="atualizandoDocStatusId == row.id" x-cloak class="h-4 w-4 animate-spin text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        x-show="row.status === 'pendente' && (row.codigo === 'CHA_DECL_EXTRAVIO_DANO_ANEXO_5D' || row.modelo_slug === 'anexo-5d')"
                                                        x-cloak
                                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-violet-200 bg-violet-50 text-violet-900 shadow-sm hover:bg-violet-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-violet-900/40 dark:bg-violet-950/40 dark:text-violet-200 dark:hover:bg-violet-900/30"
                                                        title="{{ __('Preencher declaração com o modelo PDF (Anexo 5-D, NORMAM 211)') }}"
                                                        aria-label="{{ __('Preencher declaração com o modelo PDF (Anexo 5-D, NORMAM 211)') }}"
                                                        :disabled="enviandoAnexosDocId !== null || removendoAnexoId !== null || atualizandoDocStatusId !== null || !processoId"
                                                        @click="patchDocumentoStatus(row.id, 'enviado', { declaracao_anexo_5d: true })"
                                                    >
                                                        <svg x-show="atualizandoDocStatusId != row.id" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                                        <svg x-show="atualizandoDocStatusId == row.id" x-cloak class="h-4 w-4 animate-spin text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        x-show="row.status === 'pendente' && (row.codigo === 'CHA_DECL_EXTRAVIO_MTA_3D_212' || row.modelo_slug === 'anexo-3d-extravio-cha-mta-normam212')"
                                                        x-cloak
                                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-violet-200 bg-violet-50 text-violet-900 shadow-sm hover:bg-violet-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-violet-900/40 dark:bg-violet-950/40 dark:text-violet-200 dark:hover:bg-violet-900/30"
                                                        title="{{ __('Preencher declaração com o modelo PDF (Anexo 3-D, NORMAM 212)') }}"
                                                        aria-label="{{ __('Preencher declaração com o modelo PDF (Anexo 3-D, NORMAM 212)') }}"
                                                        :disabled="enviandoAnexosDocId !== null || removendoAnexoId !== null || atualizandoDocStatusId !== null || !processoId"
                                                        @click="patchDocumentoStatus(row.id, 'enviado', { declaracao_anexo_3d: true })"
                                                    >
                                                        <svg x-show="atualizandoDocStatusId != row.id" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                                        <svg x-show="atualizandoDocStatusId == row.id" x-cloak class="h-4 w-4 animate-spin text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        x-show="row.status === 'pendente' && row.modelo_slug && row.modelo_slug !== 'anexo-2g' && row.modelo_slug !== 'anexo-5h' && row.modelo_slug !== 'anexo-5d' && row.modelo_slug !== 'anexo-3d-extravio-cha-mta-normam212'"
                                                        x-cloak
                                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-teal-200 bg-teal-50 text-teal-900 shadow-sm hover:bg-teal-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-teal-900/40 dark:bg-teal-950/40 dark:text-teal-200 dark:hover:bg-teal-900/30"
                                                        title="{{ __('Conclui o item pelo preenchimento digital do modelo. Para ver o documento, use Visualizar ou Abrir modelo. Para anexar o ficheiro assinado ou entrega em papel, use Anexar ou Físico.') }}"
                                                        aria-label="{{ __('Registar que o item foi preenchido com o modelo PDF') }}"
                                                        :disabled="enviandoAnexosDocId !== null || removendoAnexoId !== null || atualizandoDocStatusId !== null || !processoId"
                                                        @click="patchDocumentoStatus(row.id, 'enviado', { preenchido_via_modelo: true })"
                                                    >
                                                        <svg x-show="atualizandoDocStatusId != row.id" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                                        <svg x-show="atualizandoDocStatusId == row.id" x-cloak class="h-4 w-4 animate-spin text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        x-show="row.status === 'pendente' || row.status === 'dispensado' || (row.status === 'enviado' && permiteVariosAnexosFotos(row))"
                                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-slate-300 bg-white text-slate-700 shadow-sm hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                                                        x-bind:title="(row.status === 'enviado' && permiteVariosAnexosFotos(row)) ? @js(__('Anexar mais')) : @js(__('Anexar'))"
                                                        x-bind:aria-label="(row.status === 'enviado' && permiteVariosAnexosFotos(row)) ? @js(__('Anexar mais')) : @js(__('Anexar'))"
                                                        :disabled="enviandoAnexosDocId !== null || removendoAnexoId !== null || atualizandoDocStatusId !== null || !processoId"
                                                        @click="document.getElementById('nx-np-file-' + row.id)?.click()"
                                                    >
                                                        <svg x-show="enviandoAnexosDocId != row.id" class="h-4 w-4 shrink-0 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.009-.01-.01m7.364-7.364L12 10.5" /></svg>
                                                        <svg x-show="enviandoAnexosDocId == row.id" x-cloak class="h-4 w-4 shrink-0 animate-spin text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        x-show="row.status === 'pendente'"
                                                        x-cloak
                                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-amber-200 bg-amber-50 text-amber-900 shadow-sm hover:bg-amber-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-amber-900/40 dark:bg-amber-950/40 dark:text-amber-200 dark:hover:bg-amber-900/30"
                                                        title="{{ __('Documento apenas físico, sem envio digital') }}"
                                                        aria-label="{{ __('Documento apenas físico, sem envio digital') }}"
                                                        :disabled="enviandoAnexosDocId !== null || removendoAnexoId !== null || atualizandoDocStatusId !== null || !processoId"
                                                        @click="patchDocumentoStatus(row.id, 'fisico')"
                                                    >
                                                        <svg x-show="atualizandoDocStatusId != row.id" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" /><path d="M14 2v6h6" /><path d="M10 9H8M16 13H8M16 17H8M16 11H8M16 15H8M13 19H8" /></svg>
                                                        <svg x-show="atualizandoDocStatusId == row.id" x-cloak class="h-4 w-4 animate-spin text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    </button>
                                                    <a
                                                        x-show="row.status === 'enviado' && row.url_visualizar_modelo && (!row.anexos || !row.anexos.length)"
                                                        x-cloak
                                                        :href="urlAbsoluta(row.url_visualizar_modelo)"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-slate-300 bg-white text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                                                        title="{{ __('Visualizar') }}"
                                                        aria-label="{{ __('Visualizar') }}"
                                                    >
                                                        <svg class="h-4 w-4 shrink-0 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                    </a>
                                                    <a
                                                        x-show="row.status === 'enviado' && row.anexos && row.anexos.length && !permiteVariosAnexosFotos(row)"
                                                        x-cloak
                                                        :href="urlAbsoluta(row.anexos[0]?.url)"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-slate-300 bg-white text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                                                        title="{{ __('Visualizar') }}"
                                                        aria-label="{{ __('Visualizar') }}"
                                                    >
                                                        <svg class="h-4 w-4 shrink-0 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                                    </a>
                                                    <button
                                                        type="button"
                                                        x-show="row.status === 'fisico'"
                                                        x-cloak
                                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-slate-300 bg-white text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                                                        title="{{ __('Trocar anexo') }}"
                                                        aria-label="{{ __('Trocar anexo') }}"
                                                        :disabled="enviandoAnexosDocId !== null || removendoAnexoId !== null || atualizandoDocStatusId !== null || !processoId"
                                                        @click="solicitarTrocarAnexoDigital(row.id)"
                                                    >
                                                        <svg x-show="atualizandoDocStatusId != row.id" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13.5V7.5H13.5M12 5.5l2.5 2-2.5 2" /><path d="M19 10.5v6H10.5M12 15l-2.5 1.5 2.5 1.5" /></svg>
                                                        <svg x-show="atualizandoDocStatusId == row.id" x-cloak class="h-4 w-4 animate-spin text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400" x-show="checklistDocs.length === 0" x-cloak>{{ __('Nenhum documento configurado para este tipo de processo.') }}</p>
                        </div>

                        <div>
                            <x-input-label for="modal_observacoes" value="{{ __('Observações (opcional)') }}" />
                            <textarea
                                id="modal_observacoes"
                                name="observacoes"
                                rows="3"
                                class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
                                placeholder="{{ __('Notas internas, prazos desejados, referências…') }}"
                            >{{ old('observacoes') }}</textarea>
                            <x-input-error :messages="$errors->get('observacoes')" class="mt-2" />
                        </div>

                        <div
                            x-show="erroPasso2 !== ''"
                            x-cloak
                            class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200"
                            role="alert"
                        >
                            <span x-text="erroPasso2"></span>
                        </div>

                        <div class="sticky bottom-0 flex flex-col gap-2 border-t border-slate-200 bg-white pt-4 dark:border-slate-700 dark:bg-slate-900 sm:flex-row sm:justify-between">
                            <button
                                type="button"
                                class="w-full rounded-xl border border-slate-300 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800 sm:w-auto sm:px-5"
                                :disabled="enviandoPasso1 || enviandoConcluir"
                                @click="voltarPasso1()"
                            >
                                {{ __('Voltar') }}
                            </button>
                            <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                                <button
                                    type="button"
                                    class="w-full rounded-xl border border-slate-300 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800 sm:w-auto sm:px-5"
                                    @click="$store.novoProcesso.open = false"
                                >
                                    {{ __('Desistir') }}
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-slate-900 sm:w-auto sm:px-6"
                                    :disabled="enviandoConcluir || !processoId"
                                    @click="concluir()"
                                >
                                    <span x-show="!enviandoConcluir">{{ __('Concluir') }}</span>
                                    <span x-show="enviandoConcluir" x-cloak>{{ __('Salvando…') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <p class="mt-3 text-center text-xs text-slate-500 dark:text-slate-400">
                    <a href="{{ route('processos.create') }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Abrir página completa de criação') }}</a>
                </p>
            @endif
        </div>
    </div>
</div>
