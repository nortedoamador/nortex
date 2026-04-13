@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Cliente> $clientes */
    /** @var string $idPrefix */
    /** @var \App\Models\Embarcacao|null $embarcacao */
    /** @var bool $incluirFotosCadastro */
    $idPrefix = $idPrefix ?? '';
    $embarcacao = $embarcacao ?? null;
    $incluirFotosCadastro = $incluirFotosCadastro ?? true;
    $pid = fn (string $id) => $idPrefix.$id;
    $clientesSuggest = $clientes->filter(fn ($c) => filled($c->cpf))->values()->map(fn ($c) => [
        'id' => $c->id,
        'doc' => $c->documentoFormatado() ?? $c->cpf,
        'docDigits' => preg_replace('/\D/', '', (string) $c->cpf),
        'nome' => $c->nome,
    ]);
    $nxOld = function (string $key, $default = null) use ($embarcacao) {
        $def = $default;
        if ($def === null && $embarcacao !== null) {
            $def = $embarcacao->getAttribute($key);
        }
        $v = old($key, $def);
        if (in_array($key, ['inscricao_data_emissao', 'inscricao_data_vencimento'], true) && $v instanceof \Carbon\CarbonInterface) {
            return $v->format('Y-m-d');
        }

        return $v;
    };
    $cpfCampoInicial = old('cpf');
    if ($cpfCampoInicial === null || $cpfCampoInicial === '') {
        $cpfCampoInicial = $embarcacao?->cpfFormatadoTitular() ?? '';
    } else {
        $cpfCampoInicial = (string) $cpfCampoInicial;
    }

    $nxCpfPayloadId = 'nx-cpf-payload-'.bin2hex(random_bytes(8));
@endphp

<input type="hidden" id="{{ $pid('cliente_id') }}" name="cliente_id" value="{{ old('cliente_id', $embarcacao?->cliente_id) }}" />

<textarea id="{{ $nxCpfPayloadId }}" class="hidden" readonly tabindex="-1" aria-hidden="true">@json($clientesSuggest)</textarea>

@php
    use App\Enums\EmbarcacaoAreaNavegacao;
    use App\Enums\EmbarcacaoTipoNavegacao;
    use App\Enums\EmbarcacaoTipoPropulsao;

    $tipoNavForm = $nxOld('tipo_navegacao');
    $tipoNavSelectVal = $tipoNavForm instanceof EmbarcacaoTipoNavegacao ? $tipoNavForm->value : $tipoNavForm;

    $areaNavForm = $nxOld('area_navegacao');
    $areaNavVal = $areaNavForm instanceof EmbarcacaoAreaNavegacao ? $areaNavForm->value : $areaNavForm;

    $tipoParaAreas = $tipoNavSelectVal;
    if (($tipoParaAreas === null || $tipoParaAreas === '') && $areaNavVal !== null && $areaNavVal !== '') {
        if ($areaNavVal === EmbarcacaoAreaNavegacao::Interior->value) {
            $tipoParaAreas = EmbarcacaoTipoNavegacao::Interior->value;
        } elseif (in_array($areaNavVal, [EmbarcacaoAreaNavegacao::Costeira->value, EmbarcacaoAreaNavegacao::Oceanica->value], true)) {
            $tipoParaAreas = EmbarcacaoTipoNavegacao::MarAberto->value;
        }
    }

    $allowedAreaCases = match ($tipoParaAreas) {
        EmbarcacaoTipoNavegacao::Interior->value => [EmbarcacaoAreaNavegacao::Interior],
        EmbarcacaoTipoNavegacao::MarAberto->value => [EmbarcacaoAreaNavegacao::Costeira, EmbarcacaoAreaNavegacao::Oceanica],
        default => [],
    };

    $nxTipoAreaOpts = ['' => []];
    foreach (EmbarcacaoTipoNavegacao::cases() as $t) {
        $nxTipoAreaOpts[$t->value] = array_map(
            static fn (EmbarcacaoAreaNavegacao $a) => ['v' => $a->value, 'l' => $a->label()],
            $t->areasPermitidas(),
        );
    }

    $tipoPropForm = $nxOld('tipo_propulsao');
    $tipoPropEnum = $tipoPropForm instanceof EmbarcacaoTipoPropulsao
        ? $tipoPropForm
        : EmbarcacaoTipoPropulsao::tryFrom(is_string($tipoPropForm) ? $tipoPropForm : '');
    $tipoPropSelectVal = $tipoPropEnum?->value ?? '';
    $mostrarBlocoMotores = $tipoPropEnum === null || $tipoPropEnum->incluiMotor();

    $motorTabBase = 'min-w-0 flex-1 basis-0 rounded-lg border px-2 py-2.5 text-center text-xs font-semibold leading-tight transition sm:px-3 sm:text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900';
    $motorTabActive = $motorTabBase.' border-indigo-600 bg-indigo-600 text-white shadow-sm dark:border-indigo-500 dark:bg-indigo-500';
    $motorTabInactive = $motorTabBase.' border-slate-300 bg-white text-slate-800 hover:border-indigo-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:hover:border-indigo-500/50';
@endphp

<div class="md:col-span-2 flex w-full flex-col gap-6">
{{-- Seção 1 — Dados --}}
<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <h3 class="text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Dados') }}</h3>
    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-x-4 md:gap-y-4">
<div
    class="relative md:col-span-2"
    x-data="nxEmbarcacaoCpfSuggestEl('{{ $nxCpfPayloadId }}', '{{ $pid('cliente_id') }}', '')"
    data-nx-initial-q="{{ e($cpfCampoInicial) }}"
>
    <x-input-label for="{{ $pid('cpf') }}" value="CPF / CNPJ:" />
    <x-text-input
        x-ref="cpfInput"
        id="{{ $pid('cpf') }}"
        name="cpf"
        type="text"
        autocomplete="off"
        class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
        x-model="q"
        @input="filter()"
        @focus="filter()"
        @blur="onBlur()"
        @keydown="onKeydown($event)"
        placeholder="{{ __('CPF, CNPJ ou nome do cliente') }}"
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
            <template x-for="(item, idx) in filtered" :key="item.doc + '|' + item.nome + '|' + idx">
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
    <x-input-error :messages="$errors->get('cpf')" class="mt-2" />
    <x-input-error :messages="$errors->get('cliente_id')" class="mt-2" />
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Digite para filtrar por nome ou documento.') }}</p>
</div>

<div class="md:col-span-2">
    <div class="rounded-2xl border border-indigo-200/80 bg-indigo-50/70 p-4 shadow-sm dark:border-indigo-900/40 dark:bg-indigo-950/20 sm:p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <p class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-wide text-indigo-700 dark:text-indigo-200">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25A2.25 2.25 0 0 0 9.75 18.75h4.5A2.25 2.25 0 0 0 16.5 16.5v-2.25m-9 0V10.5A2.25 2.25 0 0 1 9.75 8.25h4.5A2.25 2.25 0 0 1 16.5 10.5v3.75m-9 0h9" /></svg>
                    {{ __('Inscrição na Marinha') }}
                </p>
                <p class="mt-1 text-xs text-indigo-900/80 dark:text-indigo-200/80">
                    {{ __('Marque para habilitar o número de inscrição, a data de emissão e a data de vencimento.') }}
                </p>
            </div>

            <label class="inline-flex select-none items-center gap-3 rounded-xl border border-indigo-200 bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm dark:border-indigo-900/40 dark:bg-slate-900 dark:text-white">
                <input
                    id="{{ $pid('inscrita_marinha') }}"
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                />
                {{ __('Já inscrita na Marinha?') }}
            </label>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 md:col-span-2 md:grid-cols-2 md:gap-x-4">
<div>
    <x-input-label for="{{ $pid('nome') }}" value="Nome da Embarcação:" />
    <x-text-input id="{{ $pid('nome') }}" name="nome" class="mt-1 block w-full" required value="{{ $nxOld('nome') }}" placeholder="Digite o nome da embarcação" />
    <x-input-error :messages="$errors->get('nome')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('inscricao') }}" value="Número de Inscrição:" />
    <x-text-input id="{{ $pid('inscricao') }}" name="inscricao" class="mt-1 block w-full" value="{{ $nxOld('inscricao') }}" placeholder="Digite o número de inscrição" />
    <x-input-error :messages="$errors->get('inscricao')" class="mt-2" />
</div>
</div>

<div>
    <x-input-label for="{{ $pid('inscricao_data_emissao') }}" :value="__('Data de emissão')" />
    <input
        type="text"
        id="{{ $pid('inscricao_data_emissao') }}"
        name="inscricao_data_emissao"
        value="{{ filled($nxOld('inscricao_data_emissao')) ? \Carbon\Carbon::parse((string) $nxOld('inscricao_data_emissao'))->format('d/m/Y') : '' }}"
        inputmode="numeric"
        maxlength="10"
        autocomplete="off"
        placeholder="dd/mm/aaaa"
        data-nx-mask="date-br"
        class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
    />
    <x-input-error :messages="$errors->get('inscricao_data_emissao')" class="mt-2" />
</div>
<div>
    <x-input-label for="{{ $pid('inscricao_data_vencimento') }}" :value="__('Data de vencimento')" />
    <input
        type="text"
        id="{{ $pid('inscricao_data_vencimento') }}"
        name="inscricao_data_vencimento"
        value="{{ filled($nxOld('inscricao_data_vencimento')) ? \Carbon\Carbon::parse((string) $nxOld('inscricao_data_vencimento'))->format('d/m/Y') : '' }}"
        inputmode="numeric"
        maxlength="10"
        autocomplete="off"
        placeholder="dd/mm/aaaa"
        data-nx-mask="date-br"
        class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
    />
    <x-input-error :messages="$errors->get('inscricao_data_vencimento')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('tipo') }}" value="Tipo:" />
    <select id="{{ $pid('tipo') }}" name="tipo" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
        @php
            $tipos = [
                'Balsa',
                'Barcaça',
                'Batelão',
                'Bote',
                'Caiaque',
                'Canoa',
                'Chata',
                'Draga',
                'Empurrador',
                'Escuna',
                'Flutuante',
                'Hidroavião',
                'Iate',
                'Jangada',
                'Jet Boat',
                'Lancha',
                'Laser',
                'Moto-Aquática/similar',
                'Multicasco (Catamarã, Trimarã, Tetramarã, etc)',
                'Outros',
                'Pesqueiro',
                'Pesquisa',
                'Petroleiro',
                'Plataforma Fixa',
                'Rebocador',
                'Traineira',
            ];
            $tipoOld = $nxOld('tipo');
        @endphp
        <option value="">{{ __('Selecione o tipo') }}</option>
        @foreach ($tipos as $t)
            <option value="{{ $t }}" @selected($tipoOld === $t)>{{ $t }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('atividade') }}" value="Atividade:" />
    <select id="{{ $pid('atividade') }}" name="atividade" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
        @php
            $atividades = [
                'Esporte e Recreio',
                'Transporte de Passageiros',
                'Transporte de Carga',
                'Transporte de Passageiros e Carga',
            ];
            $atividadeOld = $nxOld('atividade');
        @endphp
        <option value="">{{ __('Selecione a atividade') }}</option>
        @foreach ($atividades as $a)
            <option value="{{ $a }}" @selected($atividadeOld === $a)>{{ $a }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('atividade')" class="mt-2" />
</div>

<div>
    <label for="{{ $pid('tipo_navegacao') }}" class="block text-sm font-medium text-gray-700 dark:text-slate-300">
        {{ __('Tipo de navegação') }}:
    </label>
    <select id="{{ $pid('tipo_navegacao') }}" name="tipo_navegacao" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
        <option value="">{{ __('Selecione o tipo de navegação') }}</option>
        @foreach (EmbarcacaoTipoNavegacao::cases() as $tipoCase)
            <option value="{{ $tipoCase->value }}" @selected($tipoNavSelectVal === $tipoCase->value)>{{ $tipoCase->label() }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('tipo_navegacao')" class="mt-2" />
</div>

<div>
    <label for="{{ $pid('area_navegacao') }}" class="block text-sm font-medium text-gray-700 dark:text-slate-300">
        {{ __('Área de navegação') }}:
    </label>
    <select id="{{ $pid('area_navegacao') }}" name="area_navegacao" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
        <option value="">{{ __('Selecione a área de navegação') }}</option>
        @foreach ($allowedAreaCases as $areaCase)
            <option value="{{ $areaCase->value }}" @selected($areaNavVal === $areaCase->value)>{{ $areaCase->label() }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('area_navegacao')" class="mt-2" />
</div>

    </div>
</div>

{{-- Seção 2 — Especificações Técnicas --}}
<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <h3 class="text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Especificações Técnicas') }}</h3>
    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-x-4 md:gap-y-4">
<div>
    <x-input-label for="{{ $pid('tripulantes') }}" value="Tripulantes:" />
    <x-text-input id="{{ $pid('tripulantes') }}" name="tripulantes" inputmode="numeric" class="mt-1 block w-full" value="{{ $nxOld('tripulantes') }}" placeholder="Ex.: 2" />
    <x-input-error :messages="$errors->get('tripulantes')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('passageiros') }}" value="Passageiros:" />
    <x-text-input id="{{ $pid('passageiros') }}" name="passageiros" inputmode="numeric" class="mt-1 block w-full" value="{{ $nxOld('passageiros') }}" placeholder="Ex.: 10" />
    <x-input-error :messages="$errors->get('passageiros')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('comprimento') }}" value="Comprimento:" />
    <x-text-input id="{{ $pid('comprimento') }}" name="comprimento" class="mt-1 block w-full" value="{{ $nxOld('comprimento') }}" placeholder="Ex.: 10,00" />
    <x-input-error :messages="$errors->get('comprimento')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('boca') }}" value="Boca:" />
    <x-text-input id="{{ $pid('boca') }}" name="boca" class="mt-1 block w-full" value="{{ $nxOld('boca') }}" placeholder="{{ __('Ex.: 2,50 m') }}" />
    <x-input-error :messages="$errors->get('boca')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('pontal') }}" value="Pontal:" />
    <x-text-input id="{{ $pid('pontal') }}" name="pontal" class="mt-1 block w-full" value="{{ $nxOld('pontal') }}" placeholder="{{ __('Ex.: 1,20 m') }}" />
    <x-input-error :messages="$errors->get('pontal')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('contorno') }}" value="Contorno:" />
    <x-text-input id="{{ $pid('contorno') }}" name="contorno" class="mt-1 block w-full" value="{{ $nxOld('contorno') }}" placeholder="{{ __('Ex.: 12,00 m') }}" />
    <x-input-error :messages="$errors->get('contorno')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('calado_leve') }}" value="{{ __('Calado leve') }}:" />
    <x-text-input id="{{ $pid('calado_leve') }}" name="calado_leve" class="mt-1 block w-full" value="{{ $nxOld('calado_leve') }}" placeholder="{{ __('Ex.: 0,45 m') }}" />
    <x-input-error :messages="$errors->get('calado_leve')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('calado_carregado') }}" value="{{ __('Calado carregado') }}:" />
    <x-text-input id="{{ $pid('calado_carregado') }}" name="calado_carregado" class="mt-1 block w-full" value="{{ $nxOld('calado_carregado') }}" placeholder="{{ __('Ex.: 0,80 m') }}" />
    <x-input-error :messages="$errors->get('calado_carregado')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('arqueacao_bruta') }}" value="Arqueação Bruta:" />
    <x-text-input id="{{ $pid('arqueacao_bruta') }}" name="arqueacao_bruta" class="mt-1 block w-full" value="{{ $nxOld('arqueacao_bruta') }}" placeholder="Digite a arqueação bruta" />
    <x-input-error :messages="$errors->get('arqueacao_bruta')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('arqueacao_liquida') }}" value="Arqueação Líquida:" />
    <x-text-input id="{{ $pid('arqueacao_liquida') }}" name="arqueacao_liquida" class="mt-1 block w-full" value="{{ $nxOld('arqueacao_liquida') }}" placeholder="Digite a arqueação líquida" />
    <x-input-error :messages="$errors->get('arqueacao_liquida')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('numero_casco') }}" value="Número do Casco:" />
    <x-text-input id="{{ $pid('numero_casco') }}" name="numero_casco" class="mt-1 block w-full" value="{{ $nxOld('numero_casco') }}" placeholder="Digite o número do casco" />
    <x-input-error :messages="$errors->get('numero_casco')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('potencia_maxima_casco') }}" value="{{ __('Potência máxima do casco') }}:" />
    <x-text-input
        id="{{ $pid('potencia_maxima_casco') }}"
        name="potencia_maxima_casco"
        class="mt-1 block w-full"
        value="{{ $nxOld('potencia_maxima_casco') }}"
        placeholder="{{ __('Ex.: 200 HP') }}"
    />
    <x-input-error :messages="$errors->get('potencia_maxima_casco')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('construtor') }}" value="Construtor:" />
    <x-text-input id="{{ $pid('construtor') }}" name="construtor" class="mt-1 block w-full" value="{{ $nxOld('construtor') }}" placeholder="Digite o construtor" />
    <x-input-error :messages="$errors->get('construtor')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('ano_construcao') }}" value="Ano de Construção:" />
    <x-text-input id="{{ $pid('ano_construcao') }}" name="ano_construcao" inputmode="numeric" class="mt-1 block w-full" value="{{ $nxOld('ano_construcao') }}" placeholder="Ex.: 2020" />
    <x-input-error :messages="$errors->get('ano_construcao')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('cor_casco_ficha') }}" value="Cor do Casco:" />
    <x-text-input id="{{ $pid('cor_casco_ficha') }}" name="cor_casco_ficha" class="mt-1 block w-full" value="{{ $nxOld('cor_casco_ficha') }}" placeholder="Digite a cor do casco" />
    <x-input-error :messages="$errors->get('cor_casco_ficha')" class="mt-2" />
</div>

<div>
    <x-input-label for="{{ $pid('material_casco') }}" value="Material do Casco:" />
    <select id="{{ $pid('material_casco') }}" name="material_casco" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
        @php
            $materiaisCasco = [
                'Madeira',
                'Alumínio',
                'Aço',
                'Fibra de Vidro',
                'Fibra de Carbono',
                'Kevlar',
                'Polietileno',
                'Borracha',
                'Outros',
            ];
            $materialCascoOld = $nxOld('material_casco');
            $materialCascoOutroOld = $nxOld('material_casco_outro');
            $materialCascoEhOutro = filled($materialCascoOld) && ! in_array($materialCascoOld, $materiaisCasco, true);
            $materialCascoSelectValue = $materialCascoEhOutro ? 'Outros' : $materialCascoOld;
            $materialCascoOutroValue = $materialCascoEhOutro ? $materialCascoOld : $materialCascoOutroOld;
        @endphp
        <option value="">{{ __('Selecione o material do casco') }}</option>
        @foreach ($materiaisCasco as $m)
            <option value="{{ $m }}" @selected($materialCascoSelectValue === $m)>{{ $m }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('material_casco')" class="mt-2" />
</div>

<div id="{{ $pid('material-casco-outro-wrap') }}" class="hidden md:col-span-2">
    <x-input-label for="{{ $pid('material_casco_outro') }}" value="Material do Casco (Outros):" />
    <x-text-input
        id="{{ $pid('material_casco_outro') }}"
        name="material_casco_outro"
        class="mt-1 block w-full"
        value="{{ $materialCascoOutroValue }}"
        placeholder="Digite o material do casco"
    />
    <x-input-error :messages="$errors->get('material_casco_outro')" class="mt-2" />
</div>

    </div>
</div>

{{-- Seção 3 — Propulsão --}}
<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <h3 class="text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Propulsão') }}</h3>

    <div class="mt-4">
        <label for="{{ $pid('tipo_propulsao') }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            {{ __('Tipo de propulsão') }} <span class="text-red-600 dark:text-red-400" aria-hidden="true">*</span>
        </label>
        <select
            id="{{ $pid('tipo_propulsao') }}"
            name="tipo_propulsao"
            class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
            autocomplete="off"
        >
            <option value="">{{ __('Escolher…') }}</option>
            @foreach (EmbarcacaoTipoPropulsao::cases() as $tpCase)
                <option value="{{ $tpCase->value }}" @selected($tipoPropSelectVal === $tpCase->value)>{{ $tpCase->label() }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('tipo_propulsao')" class="mt-2" />
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ __('Em vela ou sem propulsão, a secção de motores fica oculta.') }}</p>
    </div>

@php
    $marcasMotor = [
        'Yamaha',
        'Mercury',
        'Suzuki',
        'Honda Marine',
        'Evinrude',
        'Tohatsu',
        'Hidea',
        'Parsun',
        'Volvo Penta',
        'MAN',
        'Caterpillar Marine',
        'Scania Marine',
        'Cummins Marine',
        'Yanmar',
        'Perkins Marine',
        'MerCruiser',
        'Mercury Racing',
        'Ilmor Marine',
        'MTU',
        'PCM (Pleasurecraft Marine)',
        'Branco',
        'Outros',
    ];
    $motorSlotsIn = old('motores');
    if (! is_array($motorSlotsIn)) {
        $motorSlotsIn = [0 => [], 1 => [], 2 => []];
        if ($embarcacao !== null) {
            $rawMot = $embarcacao->getAttribute('motores');
            if (is_array($rawMot) && $rawMot !== []) {
                foreach (array_values(array_slice($rawMot, 0, 3)) as $idx => $m) {
                    if ($idx > 2) {
                        break;
                    }
                    if (! is_array($m)) {
                        continue;
                    }
                    $motorSlotsIn[$idx] = [
                        'marca' => $m['marca'] ?? '',
                        'potencia' => $m['potencia'] ?? '',
                        'numero_serie' => $m['numero_serie'] ?? '',
                    ];
                }
            } else {
                $motorSlotsIn[0] = [
                    'marca' => $embarcacao->marca_motor ?? '',
                    'potencia' => $embarcacao->potencia_maxima_motor ?? '',
                    'numero_serie' => $embarcacao->numero_motor ?? '',
                ];
            }
        }
    }
    $motorSlots = [];
    for ($mi = 0; $mi < 3; $mi++) {
        $slot = array_merge(
            ['marca' => '', 'potencia' => '', 'numero_serie' => ''],
            is_array($motorSlotsIn[$mi] ?? null) ? $motorSlotsIn[$mi] : [],
        );
        $marcaVal = trim((string) ($slot['marca'] ?? ''));
        $marcaEhOutro = $marcaVal !== '' && ! in_array($marcaVal, $marcasMotor, true);
        $slot['_select_marca'] = $marcaEhOutro ? 'Outros' : $marcaVal;
        $slot['_marca_outro'] = $marcaEhOutro ? $marcaVal : '';
        $motorSlots[] = $slot;
    }

    $motorPanelActive = 0;
    $motorPanelFromError = false;
    for ($mi = 0; $mi < 3; $mi++) {
        if ($errors->has('motores.'.$mi.'.marca') || $errors->has('motores.'.$mi.'.potencia') || $errors->has('motores.'.$mi.'.numero_serie')) {
            $motorPanelActive = $mi;
            $motorPanelFromError = true;
            break;
        }
    }
    if (! $motorPanelFromError) {
        foreach ($motorSlots as $mi => $slot) {
            $temDados = trim((string) ($slot['marca'] ?? '').(string) ($slot['potencia'] ?? '').(string) ($slot['numero_serie'] ?? '')) !== '';
            if ($temDados) {
                $motorPanelActive = $mi;
                break;
            }
        }
    }
@endphp

    <div id="{{ $pid('motor-detalhes-wrap') }}" class="mt-6 border-t border-slate-200 pt-6 dark:border-slate-700 {{ $mostrarBlocoMotores ? '' : 'hidden' }}">
        <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ __('Selecione o motor:') }}</p>
        <div
            class="mt-3 flex w-full gap-2 rounded-xl bg-slate-100/90 p-1.5 ring-1 ring-slate-200/80 dark:bg-slate-800/60 dark:ring-slate-600/80"
            role="tablist"
            aria-label="{{ __('Motores da embarcação') }}"
        >
            @for ($mi = 0; $mi < 3; $mi++)
                <button
                    type="button"
                    id="{{ $pid('motor-tab-'.$mi) }}"
                    role="tab"
                    aria-selected="{{ $motorPanelActive === $mi ? 'true' : 'false' }}"
                    aria-controls="{{ $pid('motor-panel-'.$mi) }}"
                    data-motor-tab="{{ $mi }}"
                    class="{{ $motorPanelActive === $mi ? $motorTabActive : $motorTabInactive }}"
                >
                    {{ __('Motor') }} {{ $mi + 1 }}
                </button>
            @endfor
        </div>

        @foreach ($motorSlots as $mi => $slot)
            <div
                id="{{ $pid('motor-panel-'.$mi) }}"
                role="tabpanel"
                aria-labelledby="{{ $pid('motor-tab-'.$mi) }}"
                class="mt-4 rounded-lg border border-slate-200 bg-slate-50/90 p-4 dark:border-slate-600 dark:bg-slate-800/50 {{ $motorPanelActive === $mi ? '' : 'hidden' }}"
            >
                <div class="mb-4">
                    <span class="inline-flex rounded-md bg-white px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-600 ring-1 ring-slate-200/90 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-600">
                        {{ __('Motor') }} {{ $mi + 1 }}
                    </span>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-x-4 md:gap-y-4">
                    <div class="md:col-span-2">
                        <x-input-label for="{{ $pid('motor-'.$mi.'-marca') }}" value="{{ __('Marca do motor') }}:" />
                        <select
                            id="{{ $pid('motor-'.$mi.'-marca') }}"
                            name="motores[{{ $mi }}][marca]"
                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                        >
                            <option value="">{{ __('Selecione a marca do motor') }}</option>
                            @foreach ($marcasMotor as $m)
                                <option value="{{ $m }}" @selected($slot['_select_marca'] === $m)>{{ $m }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('motores.'.$mi.'.marca')" class="mt-2" />
                    </div>
                    <div id="{{ $pid('motor-'.$mi.'-marca-outro-wrap') }}" class="hidden md:col-span-2">
                        <x-input-label for="{{ $pid('motor-'.$mi.'-marca-outro') }}" value="{{ __('Marca do motor (Outros)') }}:" />
                        <x-text-input
                            id="{{ $pid('motor-'.$mi.'-marca-outro') }}"
                            name="motores[{{ $mi }}][marca_outro]"
                            class="mt-1 block w-full"
                            value="{{ $slot['_marca_outro'] }}"
                            placeholder="{{ __('Digite a marca do motor') }}"
                        />
                    </div>
                    <div>
                        <x-input-label for="{{ $pid('motor-'.$mi.'-potencia') }}" value="{{ __('Potência') }}:" />
                        <x-text-input
                            id="{{ $pid('motor-'.$mi.'-potencia') }}"
                            name="motores[{{ $mi }}][potencia]"
                            class="mt-1 block w-full"
                            value="{{ $slot['potencia'] }}"
                            placeholder="{{ __('Ex.: 200 HP') }}"
                        />
                        <x-input-error :messages="$errors->get('motores.'.$mi.'.potencia')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="{{ $pid('motor-'.$mi.'-numero-serie') }}" value="{{ __('Nº de série') }}:" />
                        <x-text-input
                            id="{{ $pid('motor-'.$mi.'-numero-serie') }}"
                            name="motores[{{ $mi }}][numero_serie]"
                            class="mt-1 block w-full"
                            value="{{ $slot['numero_serie'] }}"
                            placeholder="{{ __('Digite o número de série') }}"
                        />
                        <x-input-error :messages="$errors->get('motores.'.$mi.'.numero_serie')" class="mt-2" />
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@if ($incluirFotosCadastro)
    @include('embarcacoes.partials.fotos-embarcacao-cadastro-campos', [
        'idPrefix' => $idPrefix,
        'embarcacao' => $embarcacao ?? null,
    ])
@endif
</div>

<script>
    (() => {
        const prefix = @json($idPrefix);

        const byId = (id) => document.getElementById(prefix + id);

        const setupOutros = ({ selectId, wrapId, inputId, outrosValue = 'Outros' }) => {
            const sel = byId(selectId);
            const wrap = byId(wrapId);
            const input = byId(inputId);
            if (!sel || !wrap || !input) return;

            const sync = () => {
                const isOutros = sel.value === outrosValue;
                wrap.classList.toggle('hidden', !isOutros);
                input.disabled = !isOutros;
                if (!isOutros) input.value = '';
            };

            sync();
            sel.addEventListener('change', sync);
        };

        const nxTipoAreaOpts = @json($nxTipoAreaOpts);

        const syncTipoAreaNavegacao = () => {
            const tipoSel = byId('tipo_navegacao');
            const areaSel = byId('area_navegacao');
            if (!tipoSel || !areaSel) return;

            const tipo = tipoSel.value || '';
            const prev = areaSel.value || '';
            const rows = nxTipoAreaOpts[tipo] ?? [];

            areaSel.innerHTML = '';
            const ph = document.createElement('option');
            ph.value = '';
            ph.textContent = @json(__('Selecione a área de navegação'));
            areaSel.appendChild(ph);

            for (const row of rows) {
                const o = document.createElement('option');
                o.value = row.v;
                o.textContent = row.l;
                areaSel.appendChild(o);
            }

            if (tipo === 'interior' && rows.length === 1) {
                areaSel.value = rows[0].v;
            } else if (prev && rows.some((r) => r.v === prev)) {
                areaSel.value = prev;
            } else {
                areaSel.value = '';
            }
        };

        const tipoNavSel = byId('tipo_navegacao');
        if (tipoNavSel) {
            syncTipoAreaNavegacao();
            tipoNavSel.addEventListener('change', syncTipoAreaNavegacao);
        }

        setupOutros({
            selectId: 'material_casco',
            wrapId: 'material-casco-outro-wrap',
            inputId: 'material_casco_outro',
        });

        for (let mi = 0; mi < 3; mi++) {
            setupOutros({
                selectId: `motor-${mi}-marca`,
                wrapId: `motor-${mi}-marca-outro-wrap`,
                inputId: `motor-${mi}-marca-outro`,
            });
        }

        const motorTabActiveCls = @json($motorTabActive);
        const motorTabInactiveCls = @json($motorTabInactive);

        const showMotorPanel = (idx) => {
            const n = Number(idx);
            if (Number.isNaN(n) || n < 0 || n > 2) return;
            for (let mi = 0; mi < 3; mi++) {
                const panel = byId(`motor-panel-${mi}`);
                const tab = byId(`motor-tab-${mi}`);
                if (panel) {
                    panel.classList.toggle('hidden', mi !== n);
                }
                if (tab) {
                    tab.className = mi === n ? motorTabActiveCls : motorTabInactiveCls;
                    tab.setAttribute('aria-selected', mi === n ? 'true' : 'false');
                }
            }
        };
        for (let mi = 0; mi < 3; mi++) {
            const tab = byId(`motor-tab-${mi}`);
            tab?.addEventListener('click', () => showMotorPanel(mi));
        }
        showMotorPanel(@json($motorPanelActive));

        const tipoPropSel = byId('tipo_propulsao');
        const motorDetWrap = byId('motor-detalhes-wrap');
        const syncPropulsaoMotores = () => {
            if (!motorDetWrap) return;
            const v = tipoPropSel?.value || '';
            const mostra = v === '' || v === 'motor' || v === 'vela_motor';
            motorDetWrap.classList.toggle('hidden', !mostra);
        };
        if (tipoPropSel) {
            syncPropulsaoMotores();
            tipoPropSel.addEventListener('change', syncPropulsaoMotores);
        }

        const chkInsc = byId('inscrita_marinha');
        const inscInput = byId('inscricao');
        const emissaoInput = byId('inscricao_data_emissao');
        const vencInput = byId('inscricao_data_vencimento');

        const addYearsIso = (isoDateStr, years) => {
            if (!isoDateStr || !/^\d{4}-\d{2}-\d{2}$/.test(isoDateStr)) return '';
            const [y, m, d] = isoDateStr.split('-').map(Number);
            const dt = new Date(Date.UTC(y, m - 1, d));
            if (Number.isNaN(dt.getTime())) return '';
            dt.setUTCFullYear(dt.getUTCFullYear() + years);
            const yy = dt.getUTCFullYear();
            const mm = String(dt.getUTCMonth() + 1).padStart(2, '0');
            const dd = String(dt.getUTCDate()).padStart(2, '0');
            return `${yy}-${mm}-${dd}`;
        };

        const syncVencimento = () => {
            if (!emissaoInput || !vencInput) return;
            const em = emissaoInput.value;
            if (!em) {
                vencInput.value = '';

                return;
            }
            if (!String(vencInput.value || '').trim()) {
                vencInput.value = addYearsIso(em, 5);
            }
        };

        const setWrapDisabled = (el, disabled) => {
            if (!el) return;
            const wrap = el.closest('div');
            wrap?.classList.toggle('opacity-60', disabled);
            wrap?.classList.toggle('cursor-not-allowed', disabled);
        };

        if (chkInsc && inscInput) {
            const hasInscricaoFlow =
                inscInput.value.trim() !== '' ||
                (emissaoInput && emissaoInput.value !== '') ||
                (vencInput && vencInput.value !== '');
            if (hasInscricaoFlow) {
                chkInsc.checked = true;
            }
            const sync = () => {
                const enabled = !!chkInsc.checked;
                inscInput.disabled = !enabled;
                setWrapDisabled(inscInput, !enabled);
                if (emissaoInput) {
                    emissaoInput.disabled = !enabled;
                    setWrapDisabled(emissaoInput, !enabled);
                }
                if (vencInput) {
                    vencInput.disabled = !enabled;
                    setWrapDisabled(vencInput, !enabled);
                }
                if (!enabled) {
                    inscInput.value = '';
                    if (emissaoInput) emissaoInput.value = '';
                    if (vencInput) vencInput.value = '';
                } else {
                    syncVencimento();
                }
            };
            sync();
            chkInsc.addEventListener('change', sync);
            emissaoInput?.addEventListener('change', syncVencimento);
            emissaoInput?.addEventListener('input', syncVencimento);
        }
    })();
</script>

