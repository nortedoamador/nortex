<div
    class="contents"
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
>
    <button
        type="button"
        class="relative inline-flex h-9 items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-3.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/25 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-slate-500 dark:hover:bg-slate-800"
        @click="open = true"
        aria-haspopup="dialog"
        :aria-expanded="open"
        aria-controls="nx-embarcacoes-filtros-drawer"
    >
        <svg class="h-5 w-5 shrink-0 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.078.678A2.25 2.25 0 0 1 22 5.897v1.073a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v3.037a2.25 2.25 0 0 1-1.37 2.074l-3 1.2A2.25 2.25 0 0 1 7.5 20.745v-4.161a2.25 2.25 0 0 0-.659-1.591L1.659 7.56A2.25 2.25 0 0 1 1 5.97V4.897a2.25 2.25 0 0 1 1.922-2.219A48.507 48.507 0 0 1 12 3Z" />
        </svg>
        {{ __('Filtros') }}
        <span
            class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-indigo-600 px-1.5 text-[11px] font-bold leading-none text-white dark:bg-indigo-500"
            :class="filtrosEmbDrawerCount() > 0 ? '' : 'hidden'"
            x-text="filtrosEmbDrawerCount()"
        ></span>
    </button>

    <div
        id="nx-embarcacoes-filtros-drawer"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[60] flex justify-end"
        role="dialog"
        aria-modal="true"
        aria-labelledby="nx-embarcacoes-filtros-titulo"
    >
        <div
            class="absolute inset-0 bg-slate-900/40 backdrop-blur-[1px] dark:bg-black/50"
            @click="open = false"
            aria-hidden="true"
        ></div>
        <div
            x-show="open"
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
                <h2 id="nx-embarcacoes-filtros-titulo" class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Filtros') }}</h2>
                <button
                    type="button"
                    class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                    @click="open = false"
                >
                    <span class="sr-only">{{ __('Fechar') }}</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-4 py-5 sm:px-5">
                <div>
                    <label for="nx_emb_f_tipo" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Tipo') }}</label>
                    <select
                        id="nx_emb_f_tipo"
                        name="tipo"
                        x-model="state.tipo"
                        @change="apply()"
                        class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                    >
                        <option value="">{{ __('Todos') }}</option>
                        @foreach (($tipos ?? []) as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="nx_emb_f_atividade" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Atividade') }}</label>
                    <select
                        id="nx_emb_f_atividade"
                        name="atividade"
                        x-model="state.atividade"
                        @change="apply()"
                        class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-10 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                    >
                        <option value="">{{ __('Todas') }}</option>
                        @foreach (($atividades ?? []) as $a)
                            <option value="{{ $a }}">{{ $a }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="nx_emb_f_construtor" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Construtor') }}</label>
                    <div class="relative">
                        <input
                            id="nx_emb_f_construtor"
                            type="text"
                            name="construtor"
                            x-model="state.construtor"
                            autocomplete="off"
                            @focus="onConstrutorEmbFocus()"
                            @blur="onConstrutorEmbBlur()"
                            @keydown="onConstrutorEmbKeydown($event)"
                            @input.debounce.400ms="apply()"
                            :aria-expanded="construtorEmbOpen"
                            aria-autocomplete="list"
                            aria-controls="nx-emb-construtor-sugestoes"
                            placeholder="{{ __('Construtor') }}"
                            class="block w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-9 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                        />
                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </span>
                        <div
                            id="nx-emb-construtor-sugestoes"
                            x-show="construtorEmbOpen"
                            x-transition
                            x-cloak
                            class="absolute left-0 right-0 top-full z-[70] mt-1 max-h-56 overflow-hidden rounded-xl border border-slate-200 bg-white text-sm shadow-lg dark:border-slate-600 dark:bg-slate-900"
                            role="listbox"
                            @mousedown.prevent
                        >
                            <template x-if="(state.construtor || '').trim().length === 0">
                                <div class="px-3 py-2.5 text-xs text-slate-500 dark:text-slate-400">
                                    {{ __('Digite para buscar construtores cadastrados.') }}
                                </div>
                            </template>
                            <template x-if="(state.construtor || '').trim().length > 0 && construtorEmbMatches().length === 0">
                                <div class="px-3 py-2.5 text-xs text-slate-500 dark:text-slate-400">
                                    {{ __('Nenhum construtor encontrado.') }}
                                </div>
                            </template>
                            <ul class="max-h-52 overflow-y-auto py-1" x-show="construtorEmbMatches().length > 0">
                                <template x-for="(c, idx) in construtorEmbMatches()" :key="c + '-' + idx">
                                    <li
                                        role="option"
                                        :aria-selected="construtorEmbActive === idx"
                                        class="cursor-pointer px-3 py-2 text-slate-800 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800"
                                        :class="{ 'bg-indigo-50 text-indigo-900 dark:bg-indigo-950/50 dark:text-indigo-100': construtorEmbActive === idx }"
                                        @mouseenter="construtorEmbActive = idx"
                                        @mousedown.prevent="pickConstrutorEmb(c)"
                                        x-text="c"
                                    ></li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="nx_emb_f_ano" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Ano') }}</label>
                    <input
                        id="nx_emb_f_ano"
                        type="text"
                        name="ano_construcao"
                        inputmode="numeric"
                        x-model="state.ano_construcao"
                        @input.debounce.400ms="apply()"
                        placeholder="YYYY"
                        class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                    />
                </div>

                <div>
                    <label for="nx_emb_f_motor" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Nº do motor') }}</label>
                    <input
                        id="nx_emb_f_motor"
                        type="text"
                        name="numero_motor"
                        x-model="state.numero_motor"
                        @input.debounce.400ms="apply()"
                        class="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white"
                    />
                </div>

                <div class="space-y-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Inscrição') }}</p>
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-800 dark:text-slate-200">
                        <input
                            type="checkbox"
                            name="emb_insc"
                            value="1"
                            x-model="state.emb_insc"
                            @change="apply()"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                        />
                        <span>{{ __('Inscrita') }}</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-800 dark:text-slate-200">
                        <input
                            type="checkbox"
                            name="emb_sin"
                            value="1"
                            x-model="state.emb_sin"
                            @change="apply()"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                        />
                        <span>{{ __('Sem inscrição') }}</span>
                    </label>
                </div>

                <div class="space-y-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Alienação fiduciária') }}</p>
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-800 dark:text-slate-200">
                        <input
                            type="checkbox"
                            name="emb_ali"
                            value="1"
                            x-model="state.emb_ali"
                            @change="apply()"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                        />
                        <span>{{ __('Com alienação') }}</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-800 dark:text-slate-200">
                        <input
                            type="checkbox"
                            name="emb_sal"
                            value="1"
                            x-model="state.emb_sal"
                            @change="apply()"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                        />
                        <span>{{ __('Sem alienação') }}</span>
                    </label>
                </div>

                <div class="space-y-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Vencimento da inscrição') }}</p>
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-800 dark:text-slate-200">
                        <input
                            type="checkbox"
                            name="emb_vig"
                            value="1"
                            x-model="state.emb_vig"
                            @change="apply()"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                        />
                        <span>{{ __('Em vigor') }}</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-800 dark:text-slate-200">
                        <input
                            type="checkbox"
                            name="emb_ven"
                            value="1"
                            x-model="state.emb_ven"
                            @change="apply()"
                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                        />
                        <span>{{ __('Vencida') }}</span>
                    </label>
                </div>
            </div>

            <div class="border-t border-slate-200 bg-slate-50/90 px-4 py-4 dark:border-slate-700 dark:bg-slate-800/50 sm:px-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        @click="state.tipo = ''; state.atividade = ''; state.construtor = ''; state.ano_construcao = ''; state.numero_motor = ''; state.emb_insc = false; state.emb_sin = false; state.emb_ali = false; state.emb_sal = false; state.emb_vig = false; state.emb_ven = false; apply(); open = false"
                    >
                        {{ __('Limpar filtros') }}
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                        @click="apply(); open = false"
                    >
                        {{ __('Fechar') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
