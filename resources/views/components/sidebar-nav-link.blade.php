@props([
    'href',
    'active' => false,
    'pending' => false,
    'statusBadge' => false,
    'badgeText' => null,
    'badgeTone' => 'success', // success|warning
])

@php
    $base = 'flex min-w-0 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition';
    $activeClasses = 'bg-indigo-600 text-white shadow-md shadow-indigo-600/25';
    $idleClasses = 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800';

    $badgeTones = [
        'success' => 'bg-emerald-500/20 text-emerald-50 ring-1 ring-emerald-200/40',
        'warning' => 'bg-amber-400/20 text-amber-900 ring-1 ring-amber-300/60 dark:text-amber-200',
    ];
    $badgeClasses = $badgeTones[$badgeTone] ?? $badgeTones['success'];
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
    <span x-show="!sidebarCollapsed" x-cloak class="min-w-0 truncate">{{ $slot }}</span>

    @if ($badgeText)
        <span
            x-show="!sidebarCollapsed"
            x-cloak
            class="ml-auto inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $badgeClasses }}"
        >
            {{ $badgeText }}
        </span>
    @elseif ($statusBadge && $active)
        <span
            x-show="!sidebarCollapsed"
            x-cloak
            class="ml-auto inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $badgeTones['success'] }}"
        >
            Ativo
        </span>
    @elseif ($statusBadge && $pending)
        <span
            x-show="!sidebarCollapsed"
            x-cloak
            class="ml-auto inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $badgeTones['warning'] }}"
        >
            Pendente
        </span>
    @endif
</a>
