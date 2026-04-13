<x-app-layout title="{{ __('Processos') }}">
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Processos') }}</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Todas as etapas — arraste os cartões entre colunas') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div
                    class="inline-flex rounded-full border border-slate-200 bg-slate-100/80 p-1 shadow-sm dark:border-slate-600 dark:bg-slate-800/80"
                    role="group"
                    aria-label="{{ __('Tipo de visualização') }}"
                >
                    <a
                        href="{{ route('processos.index', ['v' => 'list']) }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200"
                        title="{{ __('Lista') }}"
                    >
                        <span class="sr-only">{{ __('Lista') }}</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 4.5h12M3.75 6.75h.008v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.008v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 4.5h.008v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </a>
                    <a
                        href="{{ route('processos.index', ['v' => 'grid']) }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200"
                        title="{{ __('Grade') }}"
                    >
                        <span class="sr-only">{{ __('Grade') }}</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 8.25V6ZM13.5 6.75h6v6h-6v-6Zm0 8.25h6v2.25A2.25 2.25 0 0 1 17.25 19.5h-2.25A2.25 2.25 0 0 1 12.75 17.25v-2.25Zm8.25-9v2.25A2.25 2.25 0 0 1 19.5 10.5h-6v-6h6A2.25 2.25 0 0 1 21.75 6ZM6 12.75h2.25A2.25 2.25 0 0 1 10.5 15v2.25A2.25 2.25 0 0 1 8.25 19.5H6a2.25 2.25 0 0 1-2.25-2.25V15A2.25 2.25 0 0 1 6 12.75Z" />
                        </svg>
                    </a>
                    <span
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white text-indigo-600 shadow dark:bg-slate-900 dark:text-indigo-400"
                        title="{{ __('Quadro completo') }}"
                    >
                        <span class="sr-only">{{ __('Quadro completo') }}</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-3v3.75m-9-3v3.75" />
                        </svg>
                    </span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        @include('processos.partials.kanban-board', [
            'colunas' => $colunas,
            'processos' => $processos,
            'podeMoverKanban' => $podeMoverKanban,
            'tituloSwalPendenciasKanban' => __('Processo com pendências'),
            'nxCienciaTextoSecundarioKanban' => __('Deseja realmente alterar o status mesmo assim?'),
        ])
    </div>
</x-app-layout>
