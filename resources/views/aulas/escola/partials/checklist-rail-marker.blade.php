@php
    $completo = $completo ?? false;
    $ativo = $ativo ?? false;
    $numero = (int) ($numero ?? 1);
@endphp
{{-- Círculo só; a linha vertical é uma única faixa absoluta no contentor do <ol> (timeline). --}}
<div class="relative z-10 flex w-10 shrink-0 justify-center pt-2 sm:w-11" aria-hidden="true">
    @if ($completo)
        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-teal-500 text-white shadow-sm ring-2 ring-teal-500/30 dark:bg-teal-600 dark:ring-teal-400/25">
            <svg class="h-4 w-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
        </span>
    @else
        <span @class([
            'flex h-9 w-9 items-center justify-center rounded-full border-2 bg-white text-sm font-bold tabular-nums shadow-sm dark:border-slate-600 dark:bg-slate-900',
            'border-slate-300 text-indigo-600 dark:border-slate-500 dark:text-indigo-400' => $ativo,
            'border-slate-200 text-slate-400 dark:text-slate-600 dark:text-slate-500' => ! $ativo,
        ])>{{ $numero }}</span>
    @endif
</div>
