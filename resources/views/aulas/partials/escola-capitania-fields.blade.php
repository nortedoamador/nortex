@props([
    'cap' => null,
])

@php
    /** @var \App\Models\EscolaCapitania|null $cap */
    $sfx = $cap ? 'u'.$cap->id : 'nova';
    $jurisdicoes = \App\Models\Habilitacao::JURISDICOES;
    $funcoes = \App\Support\EscolaAutoridadeMaritima::FUNCOES;
    $postos = \App\Support\EscolaAutoridadeMaritima::POSTOS;
@endphp

<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/50">
        <p class="mb-3 text-xs font-extrabold uppercase tracking-wide text-indigo-700 dark:text-indigo-200">
            {{ __('Capitania') }}
        </p>
        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <x-input-label :for="'cap_jur_'.$sfx" :value="__('Jurisdição (Capitania/Delegacia/Agência)')" />
                <select
                    id="cap_jur_{{ $sfx }}"
                    name="capitania_jurisdicao"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                >
                    <option value="" @selected(old('capitania_jurisdicao', $cap?->capitania_jurisdicao) === null || old('capitania_jurisdicao', $cap?->capitania_jurisdicao) === '')>{{ __('Selecione') }}</option>
                    @foreach ($jurisdicoes as $jur)
                        <option value="{{ $jur }}" @selected(old('capitania_jurisdicao', $cap?->capitania_jurisdicao) === $jur)>{{ $jur }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('capitania_jurisdicao')" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <x-input-label :for="'cap_end_'.$sfx" :value="__('Endereço (Capitania/Delegacia/Agência)')" />
                <textarea
                    id="cap_end_{{ $sfx }}"
                    name="capitania_endereco"
                    rows="2"
                    class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                >{{ old('capitania_endereco', $cap?->capitania_endereco) }}</textarea>
                <x-input-error :messages="$errors->get('capitania_endereco')" class="mt-2" />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Preencha o endereço manualmente. Confira sempre a informação oficial da Marinha do Brasil.') }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/50">
        <p class="mb-3 text-xs font-extrabold uppercase tracking-wide text-indigo-700 dark:text-indigo-200">
            {{ __('Representante da Autoridade Marítima') }}
        </p>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <x-input-label :for="'rep_func_'.$sfx" :value="__('Função')" />
                <select
                    id="rep_func_{{ $sfx }}"
                    name="representante_funcao"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                >
                    <option value="" @selected(old('representante_funcao', $cap?->representante_funcao) === null || old('representante_funcao', $cap?->representante_funcao) === '')>{{ __('Selecione') }}</option>
                    @foreach ($funcoes as $f)
                        <option value="{{ $f }}" @selected(old('representante_funcao', $cap?->representante_funcao) === $f)>{{ $f }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('representante_funcao')" class="mt-2" />
            </div>
            <div>
                <x-input-label :for="'rep_posto_'.$sfx" :value="__('Posto')" />
                <select
                    id="rep_posto_{{ $sfx }}"
                    name="representante_posto"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                >
                    <option value="" @selected(old('representante_posto', $cap?->representante_posto) === null || old('representante_posto', $cap?->representante_posto) === '')>{{ __('Selecione') }}</option>
                    @foreach ($postos as $p)
                        <option value="{{ $p }}" @selected(old('representante_posto', $cap?->representante_posto) === $p)>{{ $p }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('representante_posto')" class="mt-2" />
            </div>
            <div class="md:col-span-2">
                <x-input-label :for="'rep_nome_'.$sfx" :value="__('Nome do representante da Autoridade Marítima')" />
                <x-text-input
                    :id="'rep_nome_'.$sfx"
                    name="representante_nome"
                    class="mt-1 block w-full"
                    :value="old('representante_nome', $cap?->representante_nome)"
                />
                <x-input-error :messages="$errors->get('representante_nome')" class="mt-2" />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('A jurisdição e o endereço são os definidos em «Capitania» acima.') }}</p>
            </div>
        </div>
    </div>
</div>
