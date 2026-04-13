@props([
    'processo',
    'compact' => false,
    /** Texto curto para cards em grade */
    'short' => false,
])

@php
    $pendente = app(\App\Services\ProcessoStatusService::class)->temDocumentoObrigatorioPendente($processo);
@endphp

@if ($pendente)
    <span
        {{ $attributes->merge([
            'class' => $compact
                ? 'inline-flex w-fit items-center gap-1 rounded-md border border-orange-200/90 bg-orange-50/90 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-orange-900 dark:border-orange-800/80 dark:bg-orange-950/45 dark:text-orange-200'
                : 'inline-flex shrink-0 items-center gap-1.5 rounded-full border border-orange-200/90 bg-orange-50/90 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide text-orange-900 dark:border-orange-800/80 dark:bg-orange-950/45 dark:text-orange-200',
        ]) }}
        title="{{ __('Existem documentos obrigatórios pendentes no checklist deste processo.') }}"
    >
        <x-processo-docs-pendente-icon @class([$compact ? 'h-3.5 w-3.5' : 'h-4 w-4', 'text-orange-500 dark:text-orange-400']) />
        @if ($short)
            {{ __('Docs pendentes') }}
        @else
            {{ __('Documentos pendentes') }}
        @endif
    </span>
@endif
