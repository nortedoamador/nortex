@props([
    'idPrefix' => '',
    'required' => true,
])
@php
    use App\Models\Habilitacao;

    $idJur = $idPrefix.'jurisdicao';
@endphp
<div>
    <x-input-label for="{{ $idJur }}" value="{{ __('Jurisdição (Capitania / órgão)') }}" />
    <select
        id="{{ $idJur }}"
        name="jurisdicao"
        class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
        @if ($required) required @endif
    >
        <option value="">{{ __('Selecione…') }}</option>
        @foreach (Habilitacao::JURISDICOES as $j)
            <option value="{{ $j }}" @selected((string) old('jurisdicao') === $j)>{{ $j }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('jurisdicao')" class="mt-2" />
</div>
