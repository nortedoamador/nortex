@php
    /** @var \Illuminate\Support\Collection<int, array{id:int, doc:string, docDigits:string, nome:string}>|iterable $clientesSuggest */
    $idPrefix = $idPrefix ?? '';
    $clientesSuggest = $clientesSuggest ?? collect();
    $htmlRequired = $htmlRequired ?? true;

    $cpfCampoInicial = old('cpf');
    if ($cpfCampoInicial !== null && $cpfCampoInicial !== '') {
        $dig = preg_replace('/\D/', '', (string) $cpfCampoInicial);
        if (strlen($dig) === 11) {
            $cpfCampoInicial = substr($dig, 0, 3).'.'.substr($dig, 3, 3).'.'.substr($dig, 6, 3).'-'.substr($dig, 9, 2);
        } else {
            $cpfCampoInicial = (string) $cpfCampoInicial;
        }
    } else {
        $cpfCampoInicial = '';
    }

    $nxCpfPayloadId = 'nx-cpf-payload-'.bin2hex(random_bytes(8));
@endphp

<input type="hidden" id="{{ $idPrefix }}cliente_id" name="cliente_id" value="{{ old('cliente_id') }}" />

{{-- Textarea (não <script>): JSON em <script> pode corromper o DOM; @json já escapa < --}}
<textarea
    id="{{ $nxCpfPayloadId }}"
    class="hidden"
    readonly
    tabindex="-1"
    aria-hidden="true"
>@json($clientesSuggest)</textarea>

<div
    class="relative"
    x-data="nxEmbarcacaoCpfSuggestEl('{{ $nxCpfPayloadId }}', '{{ $idPrefix }}cliente_id', '{{ $idPrefix }}interessado_nome')"
    data-nx-initial-q="{{ e($cpfCampoInicial) }}"
>
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="{{ $idPrefix }}cpf_interessado">
        {{ __('Identificação do cliente') }}
    </label>
    <input
        type="text"
        id="{{ $idPrefix }}cpf_interessado"
        name="cpf"
        x-ref="cpfInput"
        x-model="q"
        autocomplete="off"
        placeholder="{{ __('CPF ou nome do cliente') }}"
        @if ($htmlRequired) required @endif
        :readonly="!!lockCamposPresetEmbarcacao"
        @input="if (!lockCamposPresetEmbarcacao) filter()"
        @focus="if (lockCamposPresetEmbarcacao) { $refs.cpfInput && $refs.cpfInput.blur(); return } filter()"
        @blur="onBlur()"
        @keydown="onKeydown($event)"
        class="mt-1 block min-h-[2.75rem] w-full rounded-xl border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
        :class="($root && $root.lockCamposPresetEmbarcacao)
            ? 'cursor-not-allowed border-slate-200 bg-slate-200 text-slate-600 placeholder:text-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:placeholder:text-slate-500'
            : 'border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500'"
    />
    <div
        x-show="open && !($root && $root.lockCamposPresetEmbarcacao)"
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
    <x-input-error :messages="$errors->get('cpf')" class="mt-2" />
    <x-input-error :messages="$errors->get('cliente_id')" class="mt-2" />
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400" x-show="!lockCamposPresetEmbarcacao" x-cloak>{{ __('Comece a digitar para sugerir clientes já cadastrados') }}</p>
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400" x-show="lockCamposPresetEmbarcacao" x-cloak>{{ __('Definido pela ficha da embarcação.') }}</p>
</div>

<div>
    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="{{ $idPrefix }}interessado_nome">
        {{ __('Nome completo') }}
    </label>
    <input
        type="text"
        id="{{ $idPrefix }}interessado_nome"
        name="nome_interessado"
        value="{{ old('nome_interessado') }}"
        readonly
        tabindex="-1"
        autocomplete="name"
        @if ($htmlRequired) required @endif
        class="mt-1 block min-h-[2.75rem] w-full cursor-not-allowed rounded-xl border px-3 py-2 text-sm shadow-sm"
        x-bind:class="lockCamposPresetEmbarcacao
            ? 'border-slate-200 bg-slate-200 text-slate-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-400'
            : 'border-slate-300 bg-slate-100 text-slate-700 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-300'"
    />
    <x-input-error :messages="$errors->get('nome_interessado')" class="mt-2" />
    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Preenchido ao escolher o cliente pela lista.') }}</p>
</div>
