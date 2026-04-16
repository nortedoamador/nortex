@php
    /** @var \App\Models\EscolaNautica $escola */
    /** @var string|null $empresaCnpj */
    use App\Support\DocumentoBrasil;
    use Illuminate\Support\Str;
    $cnpjDigits = $empresaCnpj ? DocumentoBrasil::apenasDigitos((string) $empresaCnpj) : '';
    $cnpjDisplay = $cnpjDigits !== '' ? DocumentoBrasil::formatarCnpj($cnpjDigits) : '';
    $nomeEscolaTrim = trim((string) $escola->nome);
    $perfilEscolaCompleto = $nomeEscolaTrim !== '' && $escola->diretor_cliente_id !== null;
    $editarPerfilInicial = ! $perfilEscolaCompleto || $errors->any();
    $temCapitanias = $escola->capitanias->isNotEmpty();
    $capitaniaCamposErro = $errors->hasAny([
        'capitania_jurisdicao', 'capitania_endereco',
        'representante_funcao', 'representante_posto', 'representante_nome',
    ]);
    $editarCapitaniasInicial = ! $temCapitanias || $capitaniaCamposErro;
@endphp

<x-app-layout :title="__('Escola Náutica — Perfil')">
    <x-slot name="header">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Escola Náutica') }}</h1>
    </x-slot>

    <x-escola-hub-frame>
        <div class="mb-6 flex flex-col gap-4 border-b border-slate-200/80 pb-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Perfil da escola — identificação, diretor e capitanias') }}</p>
            @include('aulas.partials.hub-turbo-back')
        </div>

        <div class="mx-auto max-w-5xl space-y-8">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <div
                class="w-full border border-slate-200/80 bg-white shadow-sm transition-[padding,border-radius] dark:border-slate-800 dark:bg-slate-900"
                x-data="{
                    perfilCompleto: @js($perfilEscolaCompleto),
                    editando: @js($editarPerfilInicial),
                    nomePerfilInicial: @js(old('nome', $escola->nome)),
                    cancelarEdicaoPerfil() {
                        if (!this.perfilCompleto) {
                            return;
                        }
                        const nomeEl = document.getElementById('escola_nome');
                        if (nomeEl) {
                            nomeEl.value = this.nomePerfilInicial;
                        }
                        const dirEl = this.$refs.perfilDiretorField;
                        if (dirEl && window.Alpine && typeof Alpine.$data === 'function') {
                            const d = Alpine.$data(dirEl);
                            if (d && typeof d.resetToInitial === 'function') {
                                d.resetToInitial();
                            }
                        }
                        this.editando = false;
                    },
                }"
                :class="perfilCompleto && !editando ? 'rounded-lg p-4' : 'rounded-2xl p-6'"
            >
                <h3
                    class="flex items-center gap-2 font-semibold text-slate-900 dark:text-white"
                    :class="perfilCompleto && !editando ? 'text-sm text-slate-600 dark:text-slate-300' : 'text-sm'"
                >
                    <span>{{ __('Identificação da escola') }}</span>
                    <span
                        x-show="perfilCompleto"
                        x-cloak
                        class="inline-flex shrink-0 text-emerald-600 dark:text-emerald-400"
                        title="{{ __('Cadastro completo') }}"
                    >
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span class="sr-only">{{ __('Cadastro completo') }}</span>
                    </span>
                </h3>

                <div
                    x-show="perfilCompleto && !editando"
                    x-cloak
                    class="mt-3 space-y-1.5 border-t border-slate-100 pt-3 text-sm leading-snug text-slate-600 dark:border-slate-700 dark:text-slate-400"
                >
                    <p class="truncate" title="{{ $escola->nome }}">
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('Nome') }}</span>
                        {{ $escola->nome }}
                    </p>
                    <p class="truncate">
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('CNPJ') }}</span>
                        {{ $cnpjDisplay !== '' ? $cnpjDisplay : '—' }}
                    </p>
                    <p class="line-clamp-2" title="{{ $escola->diretor?->nome }} {{ $escola->diretor?->cpf }}">
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('Diretor') }}</span>
                        {{ $escola->diretor?->nome ?? '—' }}@if (filled($escola->diretor?->cpf))<span class="text-slate-500"> — {{ $escola->diretor->cpf }}</span>@endif
                    </p>
                </div>

                <form
                    method="POST"
                    action="{{ route('aulas.escola.update') }}"
                    :class="perfilCompleto && !editando ? 'mt-2' : 'mt-4'"
                    class="block"
                >
                    @csrf
                    @method('PUT')

                    <fieldset
                        class="min-w-0 space-y-4 border-0 p-0 m-0 [&:disabled]:opacity-80"
                        @disabled($perfilEscolaCompleto && ! $editarPerfilInicial)
                        x-bind:disabled="perfilCompleto && !editando"
                        x-show="!perfilCompleto || editando"
                        x-cloak
                    >
                        <div>
                            <x-input-label for="escola_nome" :value="__('Nome da escola')" />
                            <x-text-input id="escola_nome" name="nome" class="mt-1 block w-full" required :value="old('nome', $escola->nome)" />
                            <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="cnpj_empresa" :value="__('CNPJ da empresa')" />
                            <x-text-input
                                id="cnpj_empresa"
                                type="text"
                                data-nx-mask="cnpj"
                                class="mt-1 block w-full bg-slate-50 dark:bg-slate-800/60"
                                :value="$cnpjDisplay"
                                autocomplete="off"
                                :disabled="true"
                            />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Definido no cadastro da empresa; não pode ser alterado aqui.') }}</p>
                        </div>

                        <div
                            class="space-y-2"
                            x-ref="perfilDiretorField"
                            x-data="nxEscolaDiretorField({
                                buscarCpfUrl: @js(route('alunos.buscar-cpf')),
                                initialId: @js(old('diretor_cliente_id', $escola->diretor_cliente_id)),
                                initialNome: @js(old('diretor_display_nome', $escola->diretor?->nome ?? '')),
                                initialCpf: @js(old('diretor_display_cpf', $escola->diretor?->cpf ?? '')),
                            })"
                        >
                            <x-input-label :value="__('Diretor')" />
                            <input type="hidden" name="diretor_cliente_id" x-model="diretorId" />
                            <div class="relative">
                                <div class="flex flex-wrap gap-2">
                                    <input
                                        type="text"
                                        x-ref="cpfInput"
                                        x-model="cpfQ"
                                        @input="onCpfInput()"
                                        @keydown="onKeydown($event)"
                                        @blur="onBlur()"
                                        class="min-w-[12rem] flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                                        placeholder="{{ __('CPF — pesquisar ou cadastrar') }}"
                                        autocomplete="off"
                                        inputmode="numeric"
                                    />
                                    <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" @click="openNovoCliente()">
                                        {{ __('Cadastrar novo') }}
                                    </button>
                                    <button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800" x-show="diretorId" @click="clearDiretor()">{{ __('Limpar') }}</button>
                                </div>
                                <div
                                    x-show="open"
                                    x-cloak
                                    class="mt-1 max-h-56 overflow-auto rounded-lg border border-slate-200 bg-white py-1 text-sm shadow-xl dark:border-slate-700 dark:bg-slate-900"
                                    :style="panelStyle"
                                >
                                    <template x-for="(s, idx) in sugestões" :key="s.id">
                                        <button type="button" class="flex w-full flex-col px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800" :class="idx === highlighted ? 'bg-slate-50 dark:bg-slate-800' : ''" @mousedown.prevent="pick(s)">
                                            <span class="font-medium text-slate-900 dark:text-white" x-text="s.nome"></span>
                                            <span class="text-xs text-slate-500" x-text="s.cpf"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            <p class="text-sm text-slate-600 dark:text-slate-300" x-show="diretorId">
                                <span class="font-medium" x-text="diretorNome"></span>
                                <span class="text-slate-500" x-text="diretorCpf ? ' — ' + diretorCpf : ''"></span>
                            </p>
                            <x-input-error :messages="$errors->get('diretor_cliente_id')" class="mt-2" />
                        </div>
                    </fieldset>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                            x-show="perfilCompleto && !editando"
                            x-cloak
                            @click="editando = true"
                        >
                            {{ __('Editar') }}
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                            x-show="perfilCompleto && editando"
                            x-cloak
                            @click="cancelarEdicaoPerfil()"
                        >
                            {{ __('Cancelar') }}
                        </button>
                        <x-primary-button type="submit" x-show="!perfilCompleto || editando" x-cloak>
                            {{ __('Guardar perfil') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>

            <div
                class="w-full border border-slate-200/80 bg-white shadow-sm transition-[padding,border-radius] dark:border-slate-800 dark:bg-slate-900"
                x-data="{
                    temCapitanias: @json($temCapitanias),
                    editandoCapitanias: @json($editarCapitaniasInicial),
                    abrirEdicaoCapitanias(scrollNova) {
                        this.editandoCapitanias = true;
                        if (scrollNova) {
                            this.$nextTick(() => document.getElementById('nova-capitania-anchor')?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
                        }
                    },
                }"
                :class="temCapitanias && !editandoCapitanias ? 'rounded-lg p-4' : 'rounded-2xl p-6'"
            >
                <h3
                    class="flex items-center gap-2 font-semibold text-slate-900 dark:text-white text-sm"
                    :class="temCapitanias && !editandoCapitanias ? 'text-slate-600 dark:text-slate-300' : ''"
                >
                    <span>{{ __('Jurisdição das aulas') }}</span>
                    <span
                        x-show="temCapitanias"
                        x-cloak
                        class="inline-flex shrink-0 text-emerald-600 dark:text-emerald-400"
                        title="{{ __('Cadastro completo') }}"
                    >
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span class="sr-only">{{ __('Cadastro completo') }}</span>
                    </span>
                </h3>
                <p
                    class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                    x-show="!temCapitanias || editandoCapitanias"
                    x-cloak
                >
                    {{ __('Capitania (jurisdição e endereço) e representante da Autoridade Marítima (função, posto e nome).') }}
                </p>

                <div
                    class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 dark:border-red-900/50 dark:bg-red-950/35"
                    :class="temCapitanias && !editandoCapitanias ? 'mt-3' : 'mt-4'"
                    role="note"
                >
                    <p class="text-sm font-semibold text-red-950 dark:text-red-100">
                        {{ __('Importante') }}
                    </p>
                    <p class="mt-1 text-sm leading-snug text-red-900/90 dark:text-red-100/90">
                        {{ __('As informações inseridas aqui constarão no comunicado de aula.') }}
                    </p>
                </div>

                <div
                    x-show="temCapitanias && !editandoCapitanias"
                    x-cloak
                    class="mt-3 space-y-3 border-t border-slate-100 pt-3 text-sm leading-snug text-slate-600 dark:border-slate-700 dark:text-slate-400"
                >
                    @foreach ($escola->capitanias as $cap)
                        @php
                            $repPartes = array_values(array_filter([
                                $cap->representante_funcao,
                                $cap->representante_posto,
                                $cap->representante_nome,
                            ], fn ($v) => filled($v)));
                            $repResumo = $repPartes !== [] ? implode(' — ', $repPartes) : null;
                            $endResumo = filled($cap->capitania_endereco) ? Str::limit(preg_replace('/\s+/u', ' ', trim((string) $cap->capitania_endereco)), 180) : null;
                        @endphp
                        <div class="rounded-lg border border-slate-200/80 bg-slate-50/60 p-3 dark:border-slate-700 dark:bg-slate-800/40">
                            <p class="truncate" title="{{ $cap->capitania_jurisdicao }}">
                                <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('Jurisdição') }}</span>
                                {{ filled($cap->capitania_jurisdicao) ? $cap->capitania_jurisdicao : '—' }}
                            </p>
                            <p class="mt-1 line-clamp-2" title="{{ $cap->capitania_endereco }}">
                                <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('Endereço') }}</span>
                                {{ $endResumo ?? '—' }}
                            </p>
                            <p class="mt-1 line-clamp-2" title="{{ $repResumo }}">
                                <span class="font-medium text-slate-700 dark:text-slate-300">{{ __('Representante') }}</span>
                                {{ $repResumo ?? '—' }}
                            </p>
                        </div>
                    @endforeach
                    <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3 dark:border-slate-700">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                            @click="editandoCapitanias = true"
                        >
                            {{ __('Editar') }}
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                            @click="abrirEdicaoCapitanias(true)"
                        >
                            {{ __('Adicionar capitania') }}
                        </button>
                    </div>
                </div>

                <div
                    x-show="!temCapitanias || editandoCapitanias"
                    x-cloak
                    class="mt-4 space-y-6"
                >
                    <div class="flex flex-wrap justify-end gap-2" x-show="temCapitanias && editandoCapitanias" x-cloak>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-lg border border-red-300 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-50 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300 dark:hover:bg-red-950/60"
                            @click="editandoCapitanias = false"
                        >
                            {{ __('Fechar edição') }}
                        </button>
                    </div>
                    @foreach ($escola->capitanias as $cap)
                        <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                            <form method="POST" action="{{ route('aulas.escola.capitanias.update', $cap) }}" class="space-y-4">
                                @csrf
                                @method('PATCH')
                                @include('aulas.partials.escola-capitania-fields', ['cap' => $cap])
                                <div class="flex flex-wrap gap-2">
                                    <x-secondary-button type="submit">{{ __('Atualizar') }}</x-secondary-button>
                                </div>
                            </form>
                            <form method="POST" action="{{ route('aulas.escola.capitanias.destroy', $cap) }}" class="mt-2" onsubmit="return confirm(@js(__('Remover esta capitania?')))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400">{{ __('Remover') }}</button>
                            </form>
                        </div>
                    @endforeach

                    <div id="nova-capitania-anchor" class="rounded-xl border border-dashed border-slate-300 p-4 dark:border-slate-600">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Nova capitania') }}</p>
                        <form
                            x-ref="formNovaCapitania"
                            method="POST"
                            action="{{ route('aulas.escola.capitanias.store') }}"
                            class="mt-3 space-y-4"
                        >
                            @csrf
                            @include('aulas.partials.escola-capitania-fields', ['cap' => null])
                            <div class="flex flex-wrap gap-2">
                                <x-secondary-button
                                    type="button"
                                    class="!normal-case !tracking-normal rounded-lg px-4 py-2 text-sm"
                                    @click="temCapitanias ? (editandoCapitanias = false) : $refs.formNovaCapitania.reset()"
                                >
                                    {{ __('Cancelar') }}
                                </x-secondary-button>
                                <x-primary-button type="submit">{{ __('Adicionar capitania') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Associar instrutor') }}</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Pesquise por CPF ou cadastre um novo cliente. Com um CPF completo e um único resultado, a associação é feita automaticamente.') }}</p>

                <div
                    class="relative mt-4"
                    x-data="nxEscolaInstrutorCpfField({ buscarCpfUrl: @js(route('alunos.buscar-cpf')) })"
                >
                    <form x-ref="addForm" method="POST" action="{{ route('aulas.escola.instrutores.store') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="cliente_id" x-model="pickedId" />
                        <input type="hidden" name="cha_numero" x-model="chaNumero" />
                        <input type="hidden" name="cha_categoria" x-model="chaCategoria" />
                        <input type="hidden" name="cha_data_emissao" x-model="chaDataEmissao" />
                        <input type="hidden" name="cha_data_validade" x-model="chaDataValidade" />
                        <input type="hidden" name="cha_jurisdicao" x-model="chaJurisdicao" />
                        <div class="flex flex-wrap gap-2">
                            <input
                                type="text"
                                x-ref="cpfInput"
                                x-model="cpfQ"
                                @input="onCpfInput()"
                                @keydown="onKeydown($event)"
                                @blur="onBlur()"
                                class="min-w-[12rem] flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                                placeholder="{{ __('CPF do instrutor') }}"
                                autocomplete="off"
                                inputmode="numeric"
                            />
                            <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200" @click="openNovoCliente()">
                                {{ __('Cadastrar novo') }}
                            </button>
                        </div>
                        <div
                            x-show="open"
                            x-cloak
                            class="max-h-56 overflow-auto rounded-lg border border-slate-200 bg-white py-1 text-sm shadow-xl dark:border-slate-700 dark:bg-slate-900"
                            :style="panelStyle"
                        >
                            <template x-for="(s, idx) in sugestões" :key="s.id">
                                <button type="button" class="flex w-full flex-col px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-800" :class="idx === highlighted ? 'bg-slate-50 dark:bg-slate-800' : ''" @mousedown.prevent="pick(s)">
                                    <span class="font-medium text-slate-900 dark:text-white" x-text="s.nome"></span>
                                    <span class="text-xs text-slate-500" x-text="s.cpf"></span>
                                </button>
                            </template>
                        </div>
                        @error('cliente_id')
                            <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        @foreach (['cha_numero', 'cha_categoria', 'cha_data_emissao', 'cha_data_validade', 'cha_jurisdicao'] as $chaField)
                            @error($chaField)
                                <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        @endforeach
                    </form>
                </div>
                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                    <a href="{{ route('aulas.escola.instrutores', ['sub' => 'resumo']) }}" data-turbo-frame="nx-escola-hub" data-turbo-action="advance" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Ver lista e carteiras CHA dos instrutores') }}</a>
                </p>
            </div>

            <div class="rounded-2xl border border-red-300/80 bg-gradient-to-br from-red-200/90 via-red-100/95 to-red-50 p-4 shadow-sm dark:border-red-800/50 dark:from-red-950/80 dark:via-red-950/55 dark:to-slate-900 sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:gap-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-600 text-white shadow-md shadow-red-600/30 dark:bg-red-500 dark:shadow-red-900/40" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A9 9 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A9 9 0 0 0 18 18a8.963 8.963 0 0 0-6-2.292m0-14.25v14.25" />
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1 space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm font-bold uppercase tracking-wide text-red-950 dark:text-red-50">{{ __('Plano de treinamento') }}</h2>
                            @if ($planoTreinamentoCompleto)
                                <span class="inline-flex shrink-0 text-emerald-600 dark:text-emerald-400" role="img" title="{{ __('Plano ARA e MTA: todas as durações estão definidas.') }}" aria-label="{{ __('Plano ARA e MTA: todas as durações estão definidas.') }}">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </span>
                            @else
                                <span class="inline-flex shrink-0 text-red-600 dark:text-red-400" role="img" title="{{ __('Faltam durações em ARA ou MTA: conclua o plano para emitir atestados.') }}" aria-label="{{ __('Faltam durações em ARA ou MTA: conclua o plano para emitir atestados.') }}">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </span>
                            @endif
                            <span class="inline-flex items-center rounded-full border border-amber-300/90 bg-amber-100/90 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-950 dark:border-amber-800/60 dark:bg-amber-950/50 dark:text-amber-100">{{ __('Obrigatório') }}</span>
                        </div>
                        <p class="text-xs leading-relaxed text-red-950/90 dark:text-red-100/90">
                            {{ __('A empresa deve definir as durações em minutos de cada conteúdo para poder gerar atestados de aula. São dois programas NORMAM distintos: ARA (Arrais-Amador) e MTA (Motonauta).') }}
                        </p>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2 border-t border-red-300/60 pt-4 dark:border-red-800/50">
                    <x-escola-nav-pill :href="route('aulas.atestados.index', ['tab' => 'ara'])" :active="false">{{ __('ARA — Arrais-Amador') }}</x-escola-nav-pill>
                    <x-escola-nav-pill :href="route('aulas.atestados.index', ['tab' => 'mta'])" :active="false">{{ __('MTA — Motonauta') }}</x-escola-nav-pill>
                </div>
            </div>
        </div>

        @include('aulas.partials.modal-novo-diretor-escola')
        @include('aulas.partials.modal-novo-cliente-instrutor-escola')
    </x-escola-hub-frame>
</x-app-layout>
