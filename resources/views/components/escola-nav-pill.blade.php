@props([
    'active' => false,
    'href' => '#',
    /** @var string|null Nome do turbo-frame; null desativa navegação Turbo. */
    'frame' => 'nx-escola-hub',
])

@php
    $base = 'inline-flex items-center justify-center rounded-xl border px-3 py-2 text-sm font-semibold shadow-sm transition';
    $classes = $active
        ? $base.' border-slate-200 bg-indigo-600 text-white dark:border-slate-700'
        : $base.' border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800';
@endphp

<a
    href="{{ $href }}"
    @if ($frame)
        data-turbo-frame="{{ $frame }}"
        data-turbo-action="advance"
    @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
</a>
