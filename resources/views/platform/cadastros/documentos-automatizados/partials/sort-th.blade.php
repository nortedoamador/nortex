@php
    /** @var string $column */
    /** @var string $label */
    /** @var string $sort */
    /** @var string $dir */
    /** @var string $q */
    $align = $align ?? 'left';
    $active = $sort === $column;
    $nextDir = (! $active || $dir === 'desc') ? 'asc' : 'desc';
    $url = route('platform.cadastros.documentos-automatizados.index', array_filter([
        'q' => $q !== '' ? $q : null,
        'sort' => $column,
        'dir' => $nextDir,
    ], static fn ($v) => $v !== null && $v !== ''));
    $flexJustify = $align === 'right' ? 'justify-end' : 'justify-start';
    $thAlign = $align === 'right' ? 'text-right' : 'text-left';
@endphp
<th scope="col" class="px-4 py-3 {{ $thAlign }} text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">
    <div class="flex w-full min-w-0 {{ $flexJustify }}">
        <a
            href="{{ $url }}"
            class="group inline-flex max-w-full items-center gap-1.5 rounded-lg px-1 py-0.5 -mx-1 text-inherit hover:bg-slate-100 dark:hover:bg-slate-800/80 focus:outline-none focus:ring-2 focus:ring-violet-500/30"
            aria-label="{{ __('Ordenar por :coluna', ['coluna' => $label]) }}"
        >
            <span class="truncate">{{ $label }}</span>
            <span class="shrink-0 text-slate-500 dark:text-slate-400" aria-hidden="true">
                @if ($active && $dir === 'asc')
                    <svg class="h-4 w-4 text-violet-600 dark:text-violet-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                    </svg>
                @elseif ($active && $dir === 'desc')
                    <svg class="h-4 w-4 text-violet-600 dark:text-violet-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                @else
                    <svg class="h-4 w-4 opacity-70 group-hover:opacity-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                    </svg>
                @endif
            </span>
        </a>
    </div>
</th>
