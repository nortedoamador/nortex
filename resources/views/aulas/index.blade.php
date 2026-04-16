<x-app-layout :title="__('Escola Náutica')">
    <x-slot name="header">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Escola Náutica') }}</h1>
    </x-slot>

    <x-escola-hub-frame>
        <div class="mb-6 border-b border-slate-200/80 pb-5 dark:border-slate-800">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Visão geral e ofícios de aula') }}</p>
        </div>

        @if (auth()->user()?->hasPermission('aulas.manage'))
            <div class="hidden" x-data x-init="@if ($errors->any()) $store.novaAula.open = true @endif"></div>
        @endif

        <div
            x-data="nxAulasIndex({
                baseUrl: @js(route('aulas.index')),
                initial: {
                    q: @js($busca ?? ''),
                    numero_oficio: @js($qNumero ?? ''),
                    data: @js($qData ?? ''),
                    instrutor: @js($qInstrutor ?? ''),
                    aluno: @js($qAluno ?? ''),
                    tipo_aula: @js($qTipoAula ?? ''),
                },
                initialTagsHtml: @js(view('aulas.partials.index-filtros-tags', [
                    'busca' => $busca ?? '',
                    'qData' => $qData ?? '',
                    'qNumero' => $qNumero ?? '',
                    'qTipoAula' => $qTipoAula ?? '',
                    'qInstrutor' => $qInstrutor ?? '',
                    'qAluno' => $qAluno ?? '',
                    'tiposAula' => $tiposAula ?? [],
                ])->render()),
                initialRowsHtml: @js(view('aulas.partials.index-rows', ['aulas' => $aulas])->render()),
                initialPaginationHtml: @js($aulas->hasPages() ? (string) $aulas->links() : ''),
            })"
            x-init="init()"
            @keydown.escape.window="filtrosOpen = false"
            class="mx-auto max-w-6xl space-y-4"
        >
            @include('aulas.partials.index-kpis')
            <div class="flex w-full min-w-0 flex-col gap-3 rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-4">
                <div class="flex w-full min-w-0 flex-col gap-3 sm:flex-row sm:items-center sm:gap-3">
                    <label class="relative flex min-h-12 min-w-0 flex-1">
                        <span class="sr-only">{{ __('Buscar aulas') }}</span>
                        <span class="pointer-events-none absolute inset-y-0 left-4 z-10 flex items-center text-slate-400 dark:text-slate-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </span>
                        <input
                            type="search"
                            name="q"
                            x-model="state.q"
                            @input.debounce.300ms="apply()"
                            autocomplete="off"
                            placeholder="{{ __('Ofício, local, instrutor ou aluno…') }}"
                            class="box-border h-12 w-full rounded-full border border-slate-200 bg-white py-0 pl-12 pr-4 text-sm leading-normal text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                        />
                    </label>
                    <div class="flex w-full min-w-0 shrink-0 flex-col gap-2 sm:w-auto sm:flex-row sm:items-center sm:justify-end sm:gap-2">
                        <form method="GET" action="{{ route('aulas.index') }}" class="contents" @submit.prevent>
                            @include('aulas.partials.index-filtros-drawer')
                        </form>
                        @if (auth()->user()?->hasPermission('aulas.manage'))
                            <button
                                type="button"
                                class="inline-flex h-12 min-h-12 w-full shrink-0 items-center justify-center rounded-full bg-indigo-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 sm:w-auto dark:bg-indigo-600 dark:hover:bg-indigo-500"
                                @click="$store.novaAula.open = true"
                            >
                                {{ __('Nova Aula') }}
                            </button>
                        @endif
                    </div>
                </div>
                <div class="min-h-[1px] min-w-0" @click="onTagsClick($event)">
                    <div class="min-h-[1px]" x-html="html.tags"></div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-900/40">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Nº Ofício') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Data Aula') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Local') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Tipo da aula') }}</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Alunos') }}</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Instrutores') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Status') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800" x-html="html.rows"></tbody>
                    </table>
                </div>
                <div class="border-t border-slate-200/80 p-4 dark:border-slate-800" x-ref="pagination" x-html="html.pagination"></div>
            </div>
        </div>

        @if (auth()->user()?->hasPermission('aulas.manage'))
            @include('aulas.partials.modal-nova-aula', ['tiposAula' => $tiposAula, 'escolaInstrutores' => $escolaInstrutores])
        @endif
    </x-escola-hub-frame>
</x-app-layout>
