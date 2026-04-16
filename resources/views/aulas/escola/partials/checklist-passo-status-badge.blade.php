@php
    $completo = $completo ?? false;
@endphp
@if ($completo)
    <span class="inline-flex shrink-0 items-center gap-1 self-start rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-900 dark:bg-emerald-900/50 dark:text-emerald-100">
        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
        {{ __('Completo') }}
    </span>
@else
    <span class="inline-flex shrink-0 items-center self-start rounded-full border border-amber-400/90 bg-amber-100 px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide text-amber-950 dark:border-amber-700 dark:bg-amber-950/60 dark:text-amber-100">{{ __('Pendente') }}</span>
@endif
