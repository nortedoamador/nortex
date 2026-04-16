<div class="contents">
    <button
        type="button"
        class="relative inline-flex h-12 min-h-12 shrink-0 items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/25 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-slate-500 dark:hover:bg-slate-800"
        @click="filtrosOpen = true"
        aria-haspopup="dialog"
        :aria-expanded="filtrosOpen"
        aria-controls="nx-aulas-filtros-drawer"
    >
        <svg class="h-5 w-5 shrink-0 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.078.678A2.25 2.25 0 0 1 22 5.897v1.073a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v3.037a2.25 2.25 0 0 1-1.37 2.074l-3 1.2A2.25 2.25 0 0 1 7.5 20.745v-4.161a2.25 2.25 0 0 0-.659-1.591L1.659 7.56A2.25 2.25 0 0 1 1 5.97V4.897a2.25 2.25 0 0 1 1.922-2.219A48.507 48.507 0 0 1 12 3Z" />
        </svg>
        {{ __('Filtros') }}
        <span
            class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-indigo-600 px-1.5 text-[11px] font-bold leading-none text-white dark:bg-indigo-500"
            :class="filtrosAtivosCount() > 0 ? '' : 'hidden'"
            x-text="filtrosAtivosCount()"
        ></span>
    </button>

    <div
        id="nx-aulas-filtros-drawer"
        x-show="filtrosOpen"
        x-cloak
        class="fixed inset-0 z-[60] flex justify-end"
        role="dialog"
        aria-modal="true"
        aria-labelledby="nx-aulas-filtros-titulo"
    >
        <div
            class="absolute inset-0 bg-slate-900/40 backdrop-blur-[1px] dark:bg-black/50"
            @click="filtrosOpen = false"
            aria-hidden="true"
        ></div>

        <div
            x-show="filtrosOpen"
            x-transition:enter="transition transform ease-out duration-200"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition transform ease-in duration-150"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            class="relative flex h-full w-full max-w-md flex-col border-l border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900"
            @click.stop
        >
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-4 dark:border-slate-700 sm:px-5">
                <h2 id="nx-aulas-filtros-titulo" class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Filtros') }}</h2>
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                    @click="filtrosOpen = false"
                >
                    <span class="sr-only">{{ __('Fechar') }}</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-4 py-5 sm:px-5">
                <div>
                    <x-input-label for="nx_aulas_f_data" :value="__('Data')" />
                    <input
                        id="nx_aulas_f_data"
                        name="data"
                        type="date"
                        x-model="state.data"
                        @change="apply()"
                        class="mt-1 block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-3 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                    />
                </div>

                <div>
                    <x-input-label for="nx_aulas_f_numero" :value="__('Nº Ofício')" />
                    <input
                        id="nx_aulas_f_numero"
                        name="numero_oficio"
                        type="text"
                        x-model="state.numero_oficio"
                        @input.debounce.350ms="apply()"
                        class="mt-1 block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-3 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                    />
                </div>

                <div>
                    <div class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        {{ __('Tipo da aula') }}
                    </div>

                    <div class="space-y-2">
                        <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                            <input
                                type="radio"
                                name="tipo_aula"
                                value=""
                                x-model="state.tipo_aula"
                                @change="apply()"
                                class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-950"
                            />
                            {{ __('Todos') }}
                        </label>

                        @foreach (($tiposAula ?? []) as $t)
                            <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                <input
                                    type="radio"
                                    name="tipo_aula"
                                    value="{{ $t['value'] }}"
                                    x-model="state.tipo_aula"
                                    @change="apply()"
                                    class="h-4 w-4 border-slate-300 text-indigo-600 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-950"
                                />
                                {{ $t['label'] }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <x-input-label for="nx_aulas_f_instrutor" :value="__('Instrutor')" />
                    <input
                        id="nx_aulas_f_instrutor"
                        name="instrutor"
                        type="text"
                        x-model="state.instrutor"
                        @input.debounce.350ms="apply()"
                        class="mt-1 block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-3 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                    />
                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Busca por nome ou e-mail do instrutor.') }}</p>
                </div>

                <div>
                    <x-input-label for="nx_aulas_f_aluno" :value="__('Aluno')" />
                    <input
                        id="nx_aulas_f_aluno"
                        name="aluno"
                        type="text"
                        x-model="state.aluno"
                        @input.debounce.350ms="apply()"
                        class="mt-1 block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-3 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                    />
                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Busca por nome ou CPF do aluno.') }}</p>
                </div>
            </div>

            <div class="border-t border-slate-200 bg-slate-50/90 px-4 py-4 dark:border-slate-700 dark:bg-slate-800/50 sm:px-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        @click="state.q=''; state.data=''; state.numero_oficio=''; state.instrutor=''; state.aluno=''; state.tipo_aula=''; apply()"
                    >
                        {{ __('Limpar filtros') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
