@php
    /** @var \App\Models\Habilitacao|null $habilitacao */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Cliente>|iterable $clientes */
    /** @var \Illuminate\Support\Collection<int, array{id:int, doc:string, docDigits:string, nome:string}>|iterable $clientesSuggest */
    $habilitacao = $habilitacao ?? null;
    $idPrefix = $idPrefix ?? '';
    $clientesSuggest = $clientesSuggest ?? collect();
    $nxOld = function (string $key, $default = null) use ($habilitacao) {
        $def = old($key, $default);
        if ($def === null && $habilitacao !== null) {
            $def = $habilitacao->getAttribute($key);
        }
        if (in_array($key, ['data_nascimento', 'data_emissao', 'data_validade'], true)) {
            if ($def instanceof \Carbon\CarbonInterface) {
                return $def->format('d/m/Y');
            }
            if (is_string($def) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $def)) {
                return \Carbon\Carbon::parse($def)->format('d/m/Y');
            }
        }
        if ($key === 'cpf' && $def !== null && $def !== '') {
            $dig = preg_replace('/\D/', '', (string) $def);
            if (strlen($dig) === 11) {
                return substr($dig, 0, 3).'.'.substr($dig, 3, 3).'.'.substr($dig, 6, 3).'-'.substr($dig, 9, 2);
            }
        }
        if ($key === 'numero_cha' && $def !== null && $def !== '') {
            return mb_strtoupper((string) $def, 'UTF-8');
        }

        return $def;
    };

    $cpfCampoInicial = old('cpf');
    if ($cpfCampoInicial === null || $cpfCampoInicial === '') {
        $cpfCampoInicial = $habilitacao?->cpfFormatadoTitular() ?? '';
    } else {
        $cpfCampoInicial = (string) $cpfCampoInicial;
    }

    $nxCpfPayloadId = 'nx-cpf-payload-'.bin2hex(random_bytes(8));
@endphp

<input type="hidden" id="{{ $idPrefix }}cliente_id" name="cliente_id" value="{{ $nxOld('cliente_id') }}" />
<x-input-error :messages="$errors->get('cliente_id')" class="md:col-span-3" />

<textarea id="{{ $nxCpfPayloadId }}" class="hidden" readonly tabindex="-1" aria-hidden="true">@json($clientesSuggest)</textarea>
<div
    class="relative md:col-span-3"
    x-data="nxEmbarcacaoCpfSuggestEl('{{ $nxCpfPayloadId }}', '{{ $idPrefix }}cliente_id', '{{ $idPrefix }}nome')"
    data-nx-initial-q="{{ e($cpfCampoInicial) }}"
>
    <x-input-label for="{{ $idPrefix }}cpf" value="{{ __('Identificação do cliente') }}" />
    <x-text-input
        x-ref="cpfInput"
        id="{{ $idPrefix }}cpf"
        name="cpf"
        type="text"
        autocomplete="off"
        class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
        x-model="q"
        @input="filter()"
        @focus="filter()"
        @blur="onBlur()"
        @keydown="onKeydown($event)"
        placeholder="{{ __('CPF ou nome do cliente') }}"
        required
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
            <template x-for="(item, idx) in filtered" :key="item.id + '|' + item.doc + '|' + idx">
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
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Comece a digitar para sugerir clientes já cadastrados') }}</p>
</div>

<div class="md:col-span-2">
    <x-input-label for="{{ $idPrefix }}nome" value="{{ __('Nome completo') }}" />
    <x-text-input
        id="{{ $idPrefix }}nome"
        name="nome"
        class="mt-1 block w-full cursor-not-allowed bg-slate-100 text-slate-500 ring-1 ring-slate-200 focus:ring-slate-200 dark:bg-slate-800/60 dark:text-slate-300 dark:ring-slate-700"
        :value="$nxOld('nome')"
        required
        autocomplete="name"
        readonly
        tabindex="-1"
    />
    <x-input-error :messages="$errors->get('nome')" class="mt-2" />
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Preenchido automaticamente pelo CPF.') }}</p>
</div>

<div class="md:col-span-1">
    <x-input-label for="{{ $idPrefix }}data_nascimento" value="{{ __('Data de nascimento') }}" />
    <input
        type="text"
        id="{{ $idPrefix }}data_nascimento"
        name="data_nascimento"
        value="{{ $nxOld('data_nascimento') }}"
        inputmode="numeric"
        maxlength="10"
        autocomplete="off"
        placeholder="dd/mm/aaaa"
        data-nx-mask="date-br"
        required
        class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
    />
    <x-input-error :messages="$errors->get('data_nascimento')" class="mt-2" />
</div>

<div class="md:col-span-2">
    <x-input-label for="{{ $idPrefix }}categoria" value="{{ __('Categoria') }}" />
    <select
        id="{{ $idPrefix }}categoria"
        name="categoria"
        required
        class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
    >
        <option value="">{{ __('Selecione…') }}</option>
        @foreach (\App\Models\Habilitacao::CATEGORIAS_CHA as $cat)
            <option value="{{ $cat }}" @selected((string) $nxOld('categoria') === $cat)>{{ $cat }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('categoria')" class="mt-2" />
</div>

<div class="md:col-span-1">
    <x-input-label for="{{ $idPrefix }}numero_cha" value="{{ __('Número da CHA') }}" />
    <x-text-input
        id="{{ $idPrefix }}numero_cha"
        name="numero_cha"
        class="mt-1 block w-full uppercase"
        :value="$nxOld('numero_cha')"
        required
        autocapitalize="characters"
        autocomplete="off"
        oninput="this.value = this.value.toUpperCase()"
    />
    <x-input-error :messages="$errors->get('numero_cha')" class="mt-2" />
</div>

<div class="md:col-span-1">
    <x-input-label for="{{ $idPrefix }}data_emissao" value="{{ __('Data de emissão') }}" />
    <input
        type="text"
        id="{{ $idPrefix }}data_emissao"
        name="data_emissao"
        value="{{ $nxOld('data_emissao') }}"
        inputmode="numeric"
        maxlength="10"
        autocomplete="off"
        placeholder="dd/mm/aaaa"
        data-nx-mask="date-br"
        required
        x-data="{
            brToIso(br) {
                const s = String(br || '').trim();
                const m = s.match(/^(\\d{2})\\/(\\d{2})\\/(\\d{4})$/);
                if (!m) return '';
                return `${m[3]}-${m[2]}-${m[1]}`;
            },
            isoToBr(iso) {
                const s = String(iso || '').trim();
                const m = s.match(/^(\\d{4})-(\\d{2})-(\\d{2})$/);
                if (!m) return '';
                return `${m[3]}/${m[2]}/${m[1]}`;
            },
            addYears(br, years) {
                const iso = this.brToIso(br);
                if (!iso) return '';
                const d = new Date(String(iso) + 'T00:00:00');
                if (Number.isNaN(d.getTime())) return '';
                const y = d.getFullYear() + years;
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return this.isoToBr(`${y}-${m}-${day}`);
            },
            syncValidade(force = false) {
                const em = this.$el?.value;
                const vEl = document.getElementById('{{ $idPrefix }}data_validade');
                if (!vEl) return;
                if (!em) return;
                if (!force && vEl.dataset.nxManual === '1') return;
                const next = this.addYears(em, 10);
                if (next) vEl.value = next;
            },
        }"
        x-init="syncValidade(false)"
        @change="syncValidade(true)"
        @input="syncValidade(true)"
        class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
    />
    <x-input-error :messages="$errors->get('data_emissao')" class="mt-2" />
</div>

<div class="md:col-span-1">
    <x-input-label for="{{ $idPrefix }}data_validade" value="{{ __('Vencimento') }}" />
    <input
        type="text"
        id="{{ $idPrefix }}data_validade"
        name="data_validade"
        value="{{ $nxOld('data_validade') }}"
        inputmode="numeric"
        maxlength="10"
        autocomplete="off"
        placeholder="dd/mm/aaaa"
        data-nx-mask="date-br"
        required
        @change="$el.dataset.nxManual = '1'"
        @input="$el.dataset.nxManual = '1'"
        class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
    />
    <x-input-error :messages="$errors->get('data_validade')" class="mt-2" />
</div>

<div class="md:col-span-3">
    <x-input-label for="{{ $idPrefix }}jurisdicao" value="{{ __('Jurisdição (Capitania / órgão)') }}" />
    <select
        id="{{ $idPrefix }}jurisdicao"
        name="jurisdicao"
        required
        class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
    >
        <option value="">{{ __('Selecione…') }}</option>
        @foreach (\App\Models\Habilitacao::JURISDICOES as $j)
            <option value="{{ $j }}" @selected((string) $nxOld('jurisdicao') === $j)>{{ $j }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('jurisdicao')" class="mt-2" />
</div>
