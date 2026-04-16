@php
    $edicao = $compromisso->exists;
@endphp
<x-app-layout title="{{ $tituloPagina }}">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ $tituloPagina }}</h2>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-xl space-y-4">
            <form method="POST" action="{{ $edicao ? route('admin.empresa.compromissos.update', $compromisso) : route('admin.empresa.compromissos.store') }}" class="space-y-4">
                @csrf
                @if ($edicao)
                    @method('PATCH')
                @endif

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="ec_tipo">{{ __('Tipo') }}</label>
                        <select id="ec_tipo" name="tipo" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            @foreach ($tipos as $val => $label)
                                <option value="{{ $val }}" @selected(old('tipo', $compromisso->tipo) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('tipo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="ec_titulo">{{ __('Título') }}</label>
                        <input id="ec_titulo" name="titulo" value="{{ old('titulo', $compromisso->titulo) }}" required maxlength="255" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="{{ __('Ex.: Reunião de equipa — Capitania') }}" />
                        @error('titulo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="ec_data">{{ __('Data') }}</label>
                        <input id="ec_data" type="date" name="data" value="{{ old('data', $compromisso->data?->format('Y-m-d')) }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('data')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="ec_hi">{{ __('Hora início') }}</label>
                            <input id="ec_hi" type="time" name="hora_inicio" value="{{ old('hora_inicio', $compromisso->hora_inicio ? \Illuminate\Support\Str::substr((string) $compromisso->hora_inicio, 0, 5) : '') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            @error('hora_inicio')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="ec_hf">{{ __('Hora fim') }}</label>
                            <input id="ec_hf" type="time" name="hora_fim" value="{{ old('hora_fim', $compromisso->hora_fim ? \Illuminate\Support\Str::substr((string) $compromisso->hora_fim, 0, 5) : '') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            @error('hora_fim')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="ec_local">{{ __('Local') }}</label>
                        <input id="ec_local" name="local" value="{{ old('local', $compromisso->local) }}" maxlength="255" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('local')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200" for="ec_obs">{{ __('Observações') }}</label>
                        <textarea id="ec_obs" name="observacoes" rows="3" maxlength="2000" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">{{ old('observacoes', $compromisso->observacoes) }}</textarea>
                        @error('observacoes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Guardar') }}</button>
                    <a href="{{ route('admin.empresa.compromissos.index') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Cancelar') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
