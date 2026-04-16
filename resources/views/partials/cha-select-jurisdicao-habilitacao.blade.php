@php
    $id = $id ?? 'cha_jurisdicao';
    $name = $name ?? 'cha_jurisdicao';
    $selected = (string) ($selected ?? '');
    $dataInstrutorCha = $dataInstrutorCha ?? null;
    $selectClass = 'mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white';
    $wrapperClass = $wrapperClass ?? 'md:col-span-2';
    $jurisdicoes = \App\Models\Habilitacao::JURISDICOES;
    $selectedInList = $selected === '' || in_array($selected, $jurisdicoes, true);
@endphp
<div @if ($wrapperClass !== '') class="{{ $wrapperClass }}" @endif>
    <x-input-label :for="$id" :value="__('Jurisdição (Capitania / órgão)')" />
    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($dataInstrutorCha !== null) data-instrutor-cha="{{ $dataInstrutorCha }}" @endif
        class="{{ $selectClass }}"
    >
        <option value="">{{ __('Selecione…') }}</option>
        @if (! $selectedInList && $selected !== '')
            <option value="{{ $selected }}" selected>{{ $selected }}</option>
        @endif
        @foreach ($jurisdicoes as $j)
            <option value="{{ $j }}" @selected($selected === $j)>{{ $j }}</option>
        @endforeach
    </select>
</div>
