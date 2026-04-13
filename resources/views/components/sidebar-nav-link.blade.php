@props([
    'href',
    'active' => false,
])

@php
    $base = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition';
    $activeClasses = 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25';
    $idleClasses = 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800';
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => $base.' '.($active ? $activeClasses : $idleClasses),
    ]) }}
    :class="sidebarCollapsed ? 'justify-center gap-0 px-2' : ''"
>
    <span class="shrink-0 flex h-5 w-5 items-center justify-center [&>svg]:h-5 [&>svg]:w-5">
        {{ $icon ?? '' }}
    </span>
    <span x-show="!sidebarCollapsed" x-cloak class="truncate">{{ $slot }}</span>
</a>
