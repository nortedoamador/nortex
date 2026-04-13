@props([
    'novaAbaUrl',
    'downloadUrl',
    'printUrl',
    'destroyUrl' => null,
    'destroyConfirm' => null,
])
@php
    $confirm = $destroyConfirm ?? __('Remover este anexo?');
@endphp
<div {{ $attributes->merge(['class' => 'flex shrink-0 flex-wrap items-center gap-1 py-2']) }}>
    <a
        href="{{ $novaAbaUrl }}"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 shadow-sm hover:border-indigo-300 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:border-indigo-500 dark:hover:text-indigo-300"
        title="{{ __('Nova aba') }}"
        aria-label="{{ __('Nova aba') }}"
    >
        {{-- Lucide "external-link" (equiv. ao quadrado com seta) --}}
        <svg class="pointer-events-none h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M15 3h6v6" />
            <path d="M10 14 21 3" />
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
        </svg>
    </a>
    <a
        href="{{ $downloadUrl }}"
        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 shadow-sm hover:border-indigo-300 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:border-indigo-500 dark:hover:text-indigo-300"
        title="{{ __('Baixar') }}"
        aria-label="{{ __('Baixar') }}"
    >
        {{-- Lucide "download" --}}
        <svg class="pointer-events-none h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
            <polyline points="7 10 12 15 17 10" />
            <line x1="12" x2="12" y1="15" y2="3" />
        </svg>
    </a>
    <a
        href="{{ $printUrl }}"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 shadow-sm hover:border-indigo-300 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:border-indigo-500 dark:hover:text-indigo-300"
        title="{{ __('Imprimir') }}"
        aria-label="{{ __('Imprimir') }}"
    >
        {{-- Lucide "printer" --}}
        <svg class="pointer-events-none h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
            <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6" />
            <rect width="12" height="8" x="6" y="14" rx="1" />
        </svg>
    </a>
    @if ($destroyUrl)
        <form method="POST" action="{{ $destroyUrl }}" class="inline" onsubmit="return confirm(@js($confirm));">
            @csrf
            @method('DELETE')
            <button
                type="submit"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 shadow-sm hover:border-red-300 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400 dark:hover:border-red-500 dark:hover:text-red-400"
                title="{{ __('Remover') }}"
                aria-label="{{ __('Remover') }}"
            >
                {{-- Lucide "trash-2" --}}
                <svg class="pointer-events-none h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M3 6h18" />
                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                    <line x1="10" x2="10" y1="11" y2="17" />
                    <line x1="14" x2="14" y1="11" y2="17" />
                </svg>
            </button>
        </form>
    @endif
</div>
