@php
    $prefix = $idPrefix ?? 'ec';
    $tipoAtual = old('tipo', $compromisso->tipo);
@endphp
<div x-data="{ tipo: @js($tipoAtual) }" class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="{{ $prefix }}_tipo">{{ __('Tipo') }}</label>
        <select
            id="{{ $prefix }}_tipo"
            name="tipo"
            required
            x-model="tipo"
            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
        >
            @foreach ($tipos as $val => $label)
                <option value="{{ $val }}" @selected($tipoAtual === $val)>{{ $label }}</option>
            @endforeach
        </select>
        @error('tipo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div x-show="tipo === 'outro'" x-cloak x-transition>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="{{ $prefix }}_tipo_custom">{{ __('Descreva o tipo') }}</label>
        <input
            id="{{ $prefix }}_tipo_custom"
            name="tipo_custom"
            value="{{ old('tipo_custom', $compromisso->tipo_custom) }}"
            maxlength="128"
            autocomplete="off"
            placeholder="{{ __('Ex.: Visita técnica, treinamento interno…') }}"
            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white"
            x-bind:disabled="tipo !== 'outro'"
            x-bind:required="tipo === 'outro'"
        />
        @error('tipo_custom')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="{{ $prefix }}_titulo">{{ __('Título') }}</label>
        <input id="{{ $prefix }}_titulo" name="titulo" value="{{ old('titulo', $compromisso->titulo) }}" required maxlength="255" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="{{ __('Ex.: Reunião de equipa — Capitania') }}" />
        @error('titulo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="{{ $prefix }}_data">{{ __('Data') }}</label>
        <input id="{{ $prefix }}_data" type="date" name="data" value="{{ old('data', $compromisso->data?->format('Y-m-d')) }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        @error('data')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="{{ $prefix }}_hi">{{ __('Hora início') }}</label>
            <input id="{{ $prefix }}_hi" type="time" name="hora_inicio" value="{{ old('hora_inicio', $compromisso->hora_inicio ? \Illuminate\Support\Str::substr((string) $compromisso->hora_inicio, 0, 5) : '') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            @error('hora_inicio')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="{{ $prefix }}_hf">{{ __('Hora fim') }}</label>
            <input id="{{ $prefix }}_hf" type="time" name="hora_fim" value="{{ old('hora_fim', $compromisso->hora_fim ? \Illuminate\Support\Str::substr((string) $compromisso->hora_fim, 0, 5) : '') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
            @error('hora_fim')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="{{ $prefix }}_local">{{ __('Local') }}</label>
        <input id="{{ $prefix }}_local" name="local" value="{{ old('local', $compromisso->local) }}" maxlength="255" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        @error('local')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="{{ $prefix }}_obs">{{ __('Observações') }}</label>
        <textarea id="{{ $prefix }}_obs" name="observacoes" rows="3" maxlength="2000" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">{{ old('observacoes', $compromisso->observacoes) }}</textarea>
        @error('observacoes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
