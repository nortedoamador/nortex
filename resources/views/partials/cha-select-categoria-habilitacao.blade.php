@php
    $id = $id ?? 'cha_categoria';
    $name = $name ?? 'cha_categoria';
    $selected = (string) ($selected ?? '');
    $dataInstrutorCha = $dataInstrutorCha ?? null;
    $selectClass = 'mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white';
    $categorias = \App\Models\Habilitacao::CATEGORIAS_CHA;
    $selectedInList = $selected === '' || in_array($selected, $categorias, true);
@endphp
<x-input-label :for="$id" :value="__('Categoria')" />
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
    @foreach ($categorias as $cat)
        <option value="{{ $cat }}" @selected($selected === $cat)>{{ $cat }}</option>
    @endforeach
</select>
