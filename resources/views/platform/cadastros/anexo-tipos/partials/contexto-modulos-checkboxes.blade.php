@php
    /** @var array<int, string> $selected */
    $selected = $selected ?? [];
    /** @var array<int, string> $modulosComFicheirosNaBase */
    $modulosComFicheirosNaBase = $modulosComFicheirosNaBase ?? [];
@endphp

<fieldset class="space-y-3 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
    <legend class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ __('Onde este tipo pode ser usado') }}</legend>
    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Marque os módulos em que este tipo de anexo entra (uploads ligados ao tipo global). Módulos onde já existem ficheiros ficam marcados automaticamente.') }}</p>
    <div class="space-y-2.5">
        @foreach (\App\Support\PlatformAnexoTipoContextoModulos::labels() as $key => $label)
            @php $temFicheiros = in_array($key, $modulosComFicheirosNaBase, true); @endphp
            <label @class(['flex items-start gap-3', 'cursor-pointer' => ! $temFicheiros, 'cursor-default opacity-90' => $temFicheiros])>
                <input
                    type="checkbox"
                    name="contexto_modulos[]"
                    value="{{ $key }}"
                    class="mt-0.5 rounded border-slate-300 text-violet-600 dark:border-slate-600"
                    @checked(in_array($key, $selected, true))
                    @disabled($temFicheiros)
                />
                <span class="text-sm text-slate-700 dark:text-slate-300">
                    {{ $label }}
                    @if ($temFicheiros)
                        <span class="ml-1 text-xs font-normal text-slate-500 dark:text-slate-400">({{ __('já existem ficheiros') }})</span>
                    @endif
                </span>
                @if ($temFicheiros)
                    <input type="hidden" name="contexto_modulos[]" value="{{ $key }}" />
                @endif
            </label>
        @endforeach
    </div>
    @error('contexto_modulos')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    @error('contexto_modulos.*')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
</fieldset>
