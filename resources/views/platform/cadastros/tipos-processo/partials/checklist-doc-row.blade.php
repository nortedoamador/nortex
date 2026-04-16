@php
    /** @var \App\Models\DocumentoTipo $dt */
    /** @var \App\Models\TipoProcesso $tenantTipo */
    /** @var bool $included */
    $pivot = $included ? $tenantTipo->documentoRegras->firstWhere('id', $dt->id) : null;
@endphp
<tr class="nx-checklist-tr bg-white dark:bg-slate-900" data-doc-tipo-id="{{ $dt->id }}">
    <td class="w-12 px-2 py-2 align-middle text-slate-400">
        <span
            data-nx-drag-handle
            @if ($included) draggable="true" @else draggable="false" @endif
            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-transparent text-slate-400 @if ($included) cursor-grab active:cursor-grabbing @else cursor-not-allowed opacity-40 pointer-events-none @endif"
            aria-disabled="{{ $included ? 'false' : 'true' }}"
            title="{{ __('Arrastar para ordenar') }}"
            role="button"
            tabindex="-1"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.008v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.008v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.008v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
        </span>
    </td>
    <td class="px-3 py-2 align-middle">
        <input type="checkbox" name="linhas[{{ $dt->id }}][ativo]" value="1" @checked($included) class="rounded border-slate-300 text-violet-600" />
        <input type="hidden" name="linhas[{{ $dt->id }}][documento_tipo_id]" value="{{ $dt->id }}" />
        <input
            type="hidden"
            class="nx-linha-ordem"
            name="linhas[{{ $dt->id }}][ordem]"
            value="{{ old('linhas.'.$dt->id.'.ordem', $pivot?->pivot->ordem ?? 0) }}"
        />
    </td>
    <td class="w-28 min-w-[5.5rem] px-3 py-2 align-top">
        <details class="group" @if ($errors->has('doc_fields.'.$dt->id.'.codigo')) open @endif>
            <summary
                class="cursor-pointer list-none rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-600 outline-none ring-violet-500/30 hover:bg-slate-100 focus-visible:ring-2 dark:border-slate-600 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:bg-slate-800 [&::-webkit-details-marker]:hidden"
            >
                {{ __('Código') }}
            </summary>
            <div class="mt-2 min-w-[12rem]">
                <input
                    type="text"
                    name="doc_fields[{{ $dt->id }}][codigo]"
                    value="{{ old('doc_fields.'.$dt->id.'.codigo', $dt->codigo) }}"
                    class="w-full rounded-lg border border-slate-200 px-2 py-1.5 font-mono text-xs dark:border-slate-600 dark:bg-slate-950 dark:text-white"
                    autocomplete="off"
                />
                @error('doc_fields.'.$dt->id.'.codigo')
                    <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </details>
    </td>
    <td class="min-w-0 px-3 py-2 align-top">
        <input
            type="text"
            name="doc_fields[{{ $dt->id }}][nome]"
            value="{{ old('doc_fields.'.$dt->id.'.nome', $dt->nome) }}"
            class="w-full min-h-[2.75rem] rounded-lg border border-slate-200 px-3 py-2 text-base leading-snug text-slate-900 dark:border-slate-600 dark:bg-slate-950 dark:text-white"
        />
        @error('doc_fields.'.$dt->id.'.nome')
            <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </td>
    <td class="px-3 py-2 align-top">
        <input
            type="text"
            name="doc_fields[{{ $dt->id }}][modelo_slug]"
            value="{{ old('doc_fields.'.$dt->id.'.modelo_slug', $dt->modelo_slug) }}"
            placeholder="{{ __('opcional') }}"
            class="w-full rounded-lg border border-slate-200 px-2 py-1.5 font-mono text-xs dark:border-slate-600 dark:bg-slate-950 dark:text-white"
        />
        @error('doc_fields.'.$dt->id.'.modelo_slug')
            <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </td>
    <td class="px-3 py-2 align-middle">
        <input type="checkbox" name="linhas[{{ $dt->id }}][obrigatorio]" value="1" @checked(old('linhas.'.$dt->id.'.obrigatorio', $pivot?->pivot->obrigatorio ?? true)) class="rounded border-slate-300 text-violet-600" />
    </td>
</tr>
