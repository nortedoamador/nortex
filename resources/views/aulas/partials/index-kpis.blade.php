@php
    $alunos = (int) ($kpiAlunosDistintos ?? 0);
    $aulas = (int) ($kpiTotalAulas ?? 0);
    $plano = (int) ($kpiAtestadosPares ?? 0);
    $comEnviados = (int) ($kpiComunicadosEnviados ?? 0);
    $comPendentes = (int) ($kpiComunicadosPendentes ?? 0);
@endphp

<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
    {{-- Alunos — azul --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="h-1 w-full bg-blue-600 dark:bg-blue-500" aria-hidden="true"></div>
        <div class="p-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
            <p class="mt-3 text-3xl font-bold tabular-nums text-blue-600 dark:text-blue-400">{{ $alunos }}</p>
            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Alunos') }}</p>
            <p class="mt-0.5 text-[11px] leading-snug text-slate-500 dark:text-slate-400">{{ __('Distintos em aulas') }}</p>
        </div>
    </div>

    {{-- Aulas — roxo --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="h-1 w-full bg-violet-600 dark:bg-violet-500" aria-hidden="true"></div>
        <div class="p-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-950/60 dark:text-violet-400" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A9 9 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A9 9 0 0 0 18 18a8.963 8.963 0 0 0-6-2.292m0-14.25v14.25" />
                </svg>
            </div>
            <p class="mt-3 text-3xl font-bold tabular-nums text-violet-600 dark:text-violet-400">{{ $aulas }}</p>
            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Aulas') }}</p>
            <p class="mt-0.5 text-[11px] leading-snug text-slate-500 dark:text-slate-400">{{ __('Ofícios registados') }}</p>
        </div>
    </div>

    {{-- Plano atestado — verde --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="h-1 w-full bg-emerald-600 dark:bg-emerald-500" aria-hidden="true"></div>
        <div class="p-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V12.75a9 9 0 0 0-9-9Z" />
                </svg>
            </div>
            <p class="mt-3 text-3xl font-bold tabular-nums text-emerald-600 dark:text-emerald-400">{{ $plano }}</p>
            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Plano atestado') }}</p>
            <p class="mt-0.5 text-[11px] leading-snug text-slate-500 dark:text-slate-400" title="{{ __('Itens ARA/MTA com minutos definidos no plano fixo da empresa.') }}">{{ __('Itens com duração') }}</p>
        </div>
    </div>

    {{-- Comunicados enviados — verde --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="h-1 w-full bg-emerald-600 dark:bg-emerald-500" aria-hidden="true"></div>
        <div class="p-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                </svg>
            </div>
            <p class="mt-3 text-3xl font-bold tabular-nums text-emerald-600 dark:text-emerald-400">{{ $comEnviados }}</p>
            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Comunicados enviados') }}</p>
            <p class="mt-0.5 text-[11px] leading-snug text-slate-500 dark:text-slate-400">{{ __('Total enviados') }}</p>
        </div>
    </div>

    {{-- Comunicados pendentes — laranja --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="h-1 w-full bg-amber-500 dark:bg-amber-500" aria-hidden="true"></div>
        <div class="p-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <p class="mt-3 text-3xl font-bold tabular-nums text-amber-600 dark:text-amber-400">{{ $comPendentes }}</p>
            <p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Comunicados pendentes') }}</p>
            <p class="mt-0.5 text-[11px] leading-snug text-slate-500 dark:text-slate-400">{{ __('Aguardando envio') }}</p>
        </div>
    </div>
</div>
