<x-app-layout title="{{ __('Financeiro') }}">
    <div
        id="nx-financeiro"
        class="mx-auto max-w-[1600px] space-y-6 px-4 py-6 sm:px-6 lg:px-8"
        data-resumo-url="{{ route('financeiro.api.resumo') }}"
        data-caixa-url="{{ route('financeiro.api.grafico.caixa') }}"
        data-servicos-url="{{ route('financeiro.api.grafico.servicos') }}"
        data-lista-url="{{ route('financeiro.api.lista', ['modulo' => '__MODULO__']) }}"
        data-notas-url="{{ route('financeiro.api.notas') }}"
        data-store-aulas-url="{{ route('financeiro.store.aula') }}"
        data-store-admin-url="{{ route('financeiro.store.admin_direto') }}"
        data-store-despesas-url="{{ route('financeiro.store.despesa') }}"
        data-store-parcerias-url="{{ route('financeiro.store.parceria') }}"
        data-store-engenharia-url="{{ route('financeiro.store.engenharia') }}"
        data-store-parceria-item-url="{{ route('financeiro.store.parceria.item', ['lote' => '__ID__']) }}"
        data-store-engenharia-item-url="{{ route('financeiro.store.engenharia.item', ['lote' => '__ID__']) }}"
        data-emitir-nota-url="{{ route('financeiro.notas.emitir') }}"
        data-update-aulas-url="{{ route('financeiro.update.aula', ['lancamento' => '__ID__']) }}"
        data-update-admin-url="{{ route('financeiro.update.admin_direto', ['lancamento' => '__ID__']) }}"
        data-update-despesas-url="{{ route('financeiro.update.despesa', ['lancamento' => '__ID__']) }}"
        data-update-parcerias-url="{{ route('financeiro.update.parceria', ['lote' => '__ID__']) }}"
        data-update-parceria-item-url="{{ route('financeiro.update.parceria.item', ['item' => '__ID__']) }}"
        data-update-engenharia-url="{{ route('financeiro.update.engenharia', ['lote' => '__ID__']) }}"
        data-update-engenharia-item-url="{{ route('financeiro.update.engenharia.item', ['item' => '__ID__']) }}"
        data-upload-admin-url="{{ route('financeiro.upload.admin_direto', ['lancamento' => '__ID__']) }}"
        data-upload-despesas-url="{{ route('financeiro.upload.despesa', ['lancamento' => '__ID__']) }}"
        data-upload-parcerias-url="{{ route('financeiro.upload.lote_parceria', ['lote' => '__ID__']) }}"
        data-upload-engenharia-url="{{ route('financeiro.upload.lote_engenharia', ['lote' => '__ID__']) }}"
        data-destroy-aulas-url="{{ route('financeiro.destroy.aula', ['lancamento' => '__ID__']) }}"
        data-destroy-admin-url="{{ route('financeiro.destroy.admin_direto', ['lancamento' => '__ID__']) }}"
        data-destroy-despesas-url="{{ route('financeiro.destroy.despesa', ['lancamento' => '__ID__']) }}"
        data-destroy-parcerias-url="{{ route('financeiro.destroy.parceria', ['lote' => '__ID__']) }}"
        data-destroy-parceria-item-url="{{ route('financeiro.destroy.parceria.item', ['item' => '__ID__']) }}"
        data-destroy-engenharia-url="{{ route('financeiro.destroy.engenharia', ['lote' => '__ID__']) }}"
        data-destroy-engenharia-item-url="{{ route('financeiro.destroy.engenharia.item', ['item' => '__ID__']) }}"
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Financeiro') }}</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Resumo do período selecionado') }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <label class="sr-only" for="nx-fin-ano">{{ __('Ano') }}</label>
                <select id="nx-fin-ano" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></select>

                <label class="sr-only" for="nx-fin-mes">{{ __('Mês') }}</label>
                <select id="nx-fin-mes" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                    <option value="todos">{{ __('Ano todo') }}</option>
                    <option value="01">{{ __('Janeiro') }}</option>
                    <option value="02">{{ __('Fevereiro') }}</option>
                    <option value="03">{{ __('Março') }}</option>
                    <option value="04">{{ __('Abril') }}</option>
                    <option value="05">{{ __('Maio') }}</option>
                    <option value="06">{{ __('Junho') }}</option>
                    <option value="07">{{ __('Julho') }}</option>
                    <option value="08">{{ __('Agosto') }}</option>
                    <option value="09">{{ __('Setembro') }}</option>
                    <option value="10">{{ __('Outubro') }}</option>
                    <option value="11">{{ __('Novembro') }}</option>
                    <option value="12">{{ __('Dezembro') }}</option>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button" data-nx-fin-tab="overview" class="rounded-xl border border-slate-200 bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm dark:border-slate-700">{{ __('Visão geral') }}</button>
            <button type="button" data-nx-fin-tab="aulas" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Aulas') }}</button>
            <button type="button" data-nx-fin-tab="admin_direto" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Admin Direto') }}</button>
            <button type="button" data-nx-fin-tab="despesas" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Despesas') }}</button>
            <button type="button" data-nx-fin-tab="parcerias" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Parcerias B2B') }}</button>
            <button type="button" data-nx-fin-tab="engenharia" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Engenharia Naval') }}</button>
            <button type="button" data-nx-fin-tab="notas" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Central de Notas') }}</button>
        </div>

        <div data-nx-fin-panel="overview">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Aulas Práticas') }}</p>
                <div class="mt-3 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-3xl font-bold tabular-nums text-slate-900 dark:text-white" id="nx-aulas-qtd">0</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Turmas') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200" id="nx-aulas-receita">R$ 0,00</p>
                        <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400" id="nx-aulas-lucro">R$ 0,00</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Admin Direto') }}</p>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-3xl font-bold tabular-nums text-slate-900 dark:text-white" id="nx-admin-qtd">0</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Serviços') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200" id="nx-admin-receita">R$ 0,00</p>
                        <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400" id="nx-admin-lucro">R$ 0,00</p>
                        <p class="mt-1 text-xs font-semibold text-amber-700 dark:text-amber-300" id="nx-admin-aberto">R$ 0,00</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Parcerias B2B') }}</p>
                <div class="mt-3 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-3xl font-bold tabular-nums text-slate-900 dark:text-white" id="nx-parcerias-qtd">0</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Serviços') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200" id="nx-parcerias-receita">R$ 0,00</p>
                        <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400" id="nx-parcerias-lucro">R$ 0,00</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Engenharia Naval') }}</p>
                <div class="mt-3 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-3xl font-bold tabular-nums text-slate-900 dark:text-white" id="nx-engenharia-qtd">0</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Projetos') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200" id="nx-engenharia-receita">R$ 0,00</p>
                        <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400" id="nx-engenharia-lucro">R$ 0,00</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Despesas') }}</p>
                <div class="mt-3 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-3xl font-bold tabular-nums text-slate-900 dark:text-white" id="nx-despesas-qtd">0</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Lançamentos') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-rose-600 dark:text-rose-400" id="nx-despesas-total">R$ 0,00</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 xl:col-span-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Notas Fiscais (MVP)') }}</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-amber-200/80 bg-amber-50/60 p-4 dark:border-amber-900/40 dark:bg-amber-950/20">
                        <p class="text-xs font-semibold uppercase text-amber-700 dark:text-amber-300">{{ __('Pendentes') }}</p>
                        <p class="mt-1 text-2xl font-bold text-amber-800 dark:text-amber-200" id="nx-notas-pend-qtd">0</p>
                        <p class="mt-1 text-sm font-bold text-amber-800 dark:text-amber-200" id="nx-notas-pend-val">R$ 0,00</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200/80 bg-emerald-50/60 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                        <p class="text-xs font-semibold uppercase text-emerald-700 dark:text-emerald-300">{{ __('Emitidas') }}</p>
                        <p class="mt-1 text-2xl font-bold text-emerald-800 dark:text-emerald-200" id="nx-notas-emit-qtd">0</p>
                        <p class="mt-1 text-sm font-bold text-emerald-800 dark:text-emerald-200" id="nx-notas-emit-val">R$ 0,00</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6 lg:col-span-2">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Evolução de Caixa') }}</h2>
                </div>
                <div class="relative h-72">
                    <canvas id="nx-chart-caixa"></canvas>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Volume de Serviços') }}</h2>
                </div>
                <div class="relative h-72">
                    <canvas id="nx-chart-servicos"></canvas>
                    <p id="nx-chart-servicos-empty" class="hidden absolute inset-0 flex items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                        {{ __('Nenhum serviço neste período') }}
                    </p>
                </div>
            </section>
        </div>
        </div>

        <div data-nx-fin-panel="aulas" class="hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Aulas') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Lançamentos de turmas e custos') }}</p>
                </div>
                <a class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400" href="{{ route('financeiro.export.aulas') }}">{{ __('Exportar CSV') }}</a>
            </div>

            @if (Auth::user()->hasPermission('financeiro.manage'))
                <form id="nx-form-aulas" class="mt-4 grid gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4 dark:border-slate-800 dark:bg-slate-950/30 sm:grid-cols-2 lg:grid-cols-6">
                    @csrf
                    <div class="lg:col-span-2">
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Data lançamento') }}</label>
                        <input name="data_lancamento" type="date" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Data pagamento') }}</label>
                        <input name="data_pagamento" type="date" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Alunos') }}</label>
                        <input name="qtd_alunos" type="number" min="0" value="0" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Receita (R$)') }}</label>
                        <input name="receita" type="number" min="0" step="0.01" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Barco') }}</label>
                        <input name="custo_barco" type="number" min="0" step="0.01" value="0" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Combustível') }}</label>
                        <input name="custo_combustivel" type="number" min="0" step="0.01" value="0" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Café') }}</label>
                        <input name="custo_cafe" type="number" min="0" step="0.01" value="0" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Ingresso') }}</label>
                        <input name="custo_ingresso" type="number" min="0" step="0.01" value="0" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Taxa') }}</label>
                        <input name="taxa_marinha" type="number" min="0" step="0.01" value="0" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-6">
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                <span id="nx-form-aulas-submit-label">{{ __('Lançar aula') }}</span>
                            </button>
                            <button id="nx-form-aulas-cancel" type="button" class="hidden rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                {{ __('Cancelar edição') }}
                            </button>
                        </div>
                        <p id="nx-form-aulas-error" class="mt-2 hidden text-sm text-rose-600"></p>
                    </div>
                </form>
            @endif

            <div class="mt-4 flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-center lg:justify-between">
                <input id="nx-filter-aulas-q" type="search" placeholder="{{ __('Buscar por data ou alunos') }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 sm:max-w-md lg:min-w-[200px] lg:flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <select id="nx-filter-aulas-sort" title="{{ __('Ordenar por') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <option value="data_lancamento">{{ __('Data') }}</option>
                        <option value="qtd_alunos">{{ __('Alunos') }}</option>
                        <option value="receita">{{ __('Receita') }}</option>
                        <option value="custo_total">{{ __('Custos') }}</option>
                        <option value="lucro">{{ __('Lucro') }}</option>
                    </select>
                    <select id="nx-filter-aulas-dir" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <option value="desc">{{ __('Decrescente') }}</option>
                        <option value="asc">{{ __('Crescente') }}</option>
                    </select>
                    <select id="nx-filter-aulas-limit" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-xs uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-200">
                        <tr>
                            <th class="px-4 py-3">{{ __('Data') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Alunos') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Receita') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Custos') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Lucro') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody id="nx-tbody-aulas" class="bg-white dark:bg-slate-900"></tbody>
                </table>
            </div>
            <div id="nx-pagination-aulas" class="mt-3"></div>
        </div>

        <div data-nx-fin-panel="admin_direto" class="hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Admin Direto') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Serviços administrativos e comprovantes') }}</p>
                </div>
                <a class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400" href="{{ route('financeiro.export.admin_direto') }}">{{ __('Exportar CSV') }}</a>
            </div>

            @if (Auth::user()->hasPermission('financeiro.manage'))
                <form id="nx-form-admin" class="mt-4 grid gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4 dark:border-slate-800 dark:bg-slate-950/30 sm:grid-cols-2 lg:grid-cols-6" enctype="multipart/form-data">
                    @csrf
                    <div class="lg:col-span-2">
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Data serviço') }}</label>
                        <input name="data_servico" type="date" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Data pagamento') }}</label>
                        <input name="data_pagamento" type="date" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Status') }}</label>
                        <select name="status_pagamento" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            <option value="Pago">{{ __('Pago') }}</option>
                            <option value="Em aberto" selected>{{ __('Em aberto') }}</option>
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Cliente') }}</label>
                        <input name="cliente_nome" type="text" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Serviço') }}</label>
                        <input name="servico_tipo" type="text" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Receita (R$)') }}</label>
                        <input name="receita" type="number" min="0" step="0.01" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Taxa') }}</label>
                        <input name="taxa_marinha" type="number" min="0" step="0.01" value="0" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Envio') }}</label>
                        <input name="custo_envio" type="number" min="0" step="0.01" value="0" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Comprovante (opcional)') }}</label>
                        <input name="comprovante" type="file" accept="image/*,application/pdf" class="mt-1 w-full text-sm text-slate-600 dark:text-slate-200">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-6">
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                <span id="nx-form-admin-submit-label">{{ __('Lançar serviço') }}</span>
                            </button>
                            <button id="nx-form-admin-cancel" type="button" class="hidden rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                {{ __('Cancelar edição') }}
                            </button>
                        </div>
                        <p id="nx-form-admin-error" class="mt-2 hidden text-sm text-rose-600"></p>
                    </div>
                </form>
            @endif

            <div class="mt-4 flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-center lg:justify-between">
                <input id="nx-filter-admin-q" type="search" placeholder="{{ __('Buscar cliente, serviço ou status') }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 sm:max-w-md lg:min-w-[200px] lg:flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <select id="nx-filter-admin-sort" title="{{ __('Ordenar por') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <option value="data_servico">{{ __('Data serviço') }}</option>
                        <option value="data_pagamento">{{ __('Data pagamento') }}</option>
                        <option value="cliente_nome">{{ __('Cliente') }}</option>
                        <option value="servico_tipo">{{ __('Serviço') }}</option>
                        <option value="status_pagamento">{{ __('Status') }}</option>
                        <option value="receita">{{ __('Receita') }}</option>
                        <option value="custo_total">{{ __('Custos') }}</option>
                        <option value="lucro">{{ __('Lucro') }}</option>
                    </select>
                    <select id="nx-filter-admin-dir" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <option value="desc">{{ __('Decrescente') }}</option>
                        <option value="asc">{{ __('Crescente') }}</option>
                    </select>
                    <select id="nx-filter-admin-limit" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-xs uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-200">
                        <tr>
                            <th class="px-4 py-3">{{ __('Data') }}</th>
                            <th class="px-4 py-3">{{ __('Cliente') }}</th>
                            <th class="px-4 py-3">{{ __('Serviço') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Receita') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Custos') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Lucro') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Comprovante') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody id="nx-tbody-admin" class="bg-white dark:bg-slate-900"></tbody>
                </table>
            </div>
            <div id="nx-pagination-admin_direto" class="mt-3"></div>
        </div>

        <div data-nx-fin-panel="despesas" class="hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Despesas') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Saídas e notas anexas') }}</p>
                </div>
                <a class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400" href="{{ route('financeiro.export.despesas') }}">{{ __('Exportar CSV') }}</a>
            </div>

            @if (Auth::user()->hasPermission('financeiro.manage'))
                <form id="nx-form-despesas" class="mt-4 grid gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4 dark:border-slate-800 dark:bg-slate-950/30 sm:grid-cols-2 lg:grid-cols-6" enctype="multipart/form-data">
                    @csrf
                    <div class="lg:col-span-2">
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Data lançamento') }}</label>
                        <input name="data_lancamento" type="date" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Data pagamento') }}</label>
                        <input name="data_pagamento" type="date" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Valor (R$)') }}</label>
                        <input name="valor" type="number" min="0" step="0.01" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Descrição') }}</label>
                        <input name="descricao" type="text" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-2">
                        <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Nota (opcional)') }}</label>
                        <input name="nota" type="file" accept="image/*,application/pdf" class="mt-1 w-full text-sm text-slate-600 dark:text-slate-200">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-6 flex items-center gap-2 rounded-lg border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900">
                        <input id="nx-despesa-fixa" name="fixa" type="checkbox" value="1" class="h-4 w-4 rounded border-slate-300 text-indigo-600">
                        <label for="nx-despesa-fixa" class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ __('Despesa fixa (replicar 12 meses)') }}</label>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-6">
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                <span id="nx-form-despesas-submit-label">{{ __('Lançar despesa') }}</span>
                            </button>
                            <button id="nx-form-despesas-cancel" type="button" class="hidden rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                {{ __('Cancelar edição') }}
                            </button>
                        </div>
                        <p id="nx-form-despesas-error" class="mt-2 hidden text-sm text-rose-600"></p>
                    </div>
                </form>
            @endif

            <div class="mt-4 flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-center lg:justify-between">
                <input id="nx-filter-despesas-q" type="search" placeholder="{{ __('Buscar descrição') }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 sm:max-w-md lg:min-w-[200px] lg:flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <select id="nx-filter-despesas-sort" title="{{ __('Ordenar por') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <option value="data_lancamento">{{ __('Data') }}</option>
                        <option value="valor">{{ __('Valor') }}</option>
                        <option value="descricao">{{ __('Descrição') }}</option>
                    </select>
                    <select id="nx-filter-despesas-dir" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <option value="desc">{{ __('Decrescente') }}</option>
                        <option value="asc">{{ __('Crescente') }}</option>
                    </select>
                    <select id="nx-filter-despesas-limit" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-xs uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-200">
                        <tr>
                            <th class="px-4 py-3">{{ __('Data') }}</th>
                            <th class="px-4 py-3">{{ __('Descrição') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Valor') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Nota') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody id="nx-tbody-despesas" class="bg-white dark:bg-slate-900"></tbody>
                </table>
            </div>
            <div id="nx-pagination-despesas" class="mt-3"></div>
        </div>

        <div data-nx-fin-panel="parcerias" class="hidden space-y-4">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Parcerias B2B') }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Lotes mensais e serviços vinculados') }}</p>
                    </div>
                    <a class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400" href="{{ route('financeiro.export.parcerias') }}">{{ __('Exportar CSV') }}</a>
                </div>

                @if (Auth::user()->hasPermission('financeiro.manage'))
                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                        <form id="nx-form-parcerias-lote" class="grid gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4 dark:border-slate-800 dark:bg-slate-950/30" enctype="multipart/form-data">
                            @csrf
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Novo lote') }}</h3>
                            <div>
                                <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Mês referência') }}</label>
                                <input name="mes_referencia" type="month" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Empresa parceira') }}</label>
                                <input name="empresa_parceira" type="text" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Status') }}</label>
                                <select name="status_pagamento" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                    <option value="Em aberto" selected>{{ __('Em aberto') }}</option>
                                    <option value="Pago">{{ __('Pago') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Comprovante') }}</label>
                                <input name="comprovante" type="file" accept="image/*,application/pdf" class="mt-1 w-full text-sm text-slate-600 dark:text-slate-200">
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                    <span id="nx-form-parcerias-lote-submit-label">{{ __('Criar lote') }}</span>
                                </button>
                                <button id="nx-form-parcerias-lote-cancel" type="button" class="hidden rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                    {{ __('Cancelar edição') }}
                                </button>
                            </div>
                            <p id="nx-form-parcerias-lote-error" class="hidden text-sm text-rose-600"></p>
                        </form>

                        <form id="nx-form-parcerias-item" class="grid gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4 dark:border-slate-800 dark:bg-slate-950/30">
                            @csrf
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Novo serviço no lote') }}</h3>
                            <div>
                                <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Lote') }}</label>
                                <select id="nx-parcerias-lote-id" name="lote_id" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required></select>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Data lançamento') }}</label>
                                    <input name="data_lancamento" type="date" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold uppercase text-slate-500">{{ __('Data pagamento') }}</label>
                                    <input name="data_pagamento" type="date" class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                </div>
                            </div>
                            <input name="cliente_nome" type="text" placeholder="{{ __('Cliente') }}" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                            <input name="servico_tipo" type="text" placeholder="{{ __('Serviço') }}" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <input name="receita" type="number" min="0" step="0.01" placeholder="{{ __('Receita') }}" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                                <input name="taxa_marinha" type="number" min="0" step="0.01" value="0" placeholder="{{ __('Taxa') }}" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                <input name="custo_envio" type="number" min="0" step="0.01" value="0" placeholder="{{ __('Envio') }}" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                    <span id="nx-form-parcerias-item-submit-label">{{ __('Adicionar serviço') }}</span>
                                </button>
                                <button id="nx-form-parcerias-item-cancel" type="button" class="hidden rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                    {{ __('Cancelar edição') }}
                                </button>
                            </div>
                            <p id="nx-form-parcerias-item-error" class="hidden text-sm text-rose-600"></p>
                        </form>
                    </div>
                @endif

                <div class="mt-4 flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-center lg:justify-between">
                    <input id="nx-filter-parcerias-q" type="search" placeholder="{{ __('Buscar empresa parceira') }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 sm:max-w-md lg:min-w-[200px] lg:flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <select id="nx-filter-parcerias-sort" title="{{ __('Ordenar por') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <option value="mes_referencia">{{ __('Mês referência') }}</option>
                            <option value="empresa_parceira">{{ __('Parceiro') }}</option>
                            <option value="status_pagamento">{{ __('Status') }}</option>
                        </select>
                        <select id="nx-filter-parcerias-dir" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <option value="desc">{{ __('Decrescente') }}</option>
                            <option value="asc">{{ __('Crescente') }}</option>
                        </select>
                        <select id="nx-filter-parcerias-limit" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                        </select>
                    </div>
                </div>
                <div id="nx-parcerias-lista" class="mt-4 space-y-3"></div>
                <div id="nx-pagination-parcerias" class="mt-3"></div>
            </div>
        </div>

        <div data-nx-fin-panel="engenharia" class="hidden space-y-4">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Engenharia Naval') }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Lotes e projetos/serviços vinculados') }}</p>
                    </div>
                    <a class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400" href="{{ route('financeiro.export.engenharia') }}">{{ __('Exportar CSV') }}</a>
                </div>

                @if (Auth::user()->hasPermission('financeiro.manage'))
                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                        <form id="nx-form-engenharia-lote" class="grid gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4 dark:border-slate-800 dark:bg-slate-950/30" enctype="multipart/form-data">
                            @csrf
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Novo lote') }}</h3>
                            <input name="mes_referencia" type="month" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                            <input name="empresa_parceira" type="text" placeholder="{{ __('Empresa/cliente') }}" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                            <select name="status_pagamento" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                                <option value="Em aberto" selected>{{ __('Em aberto') }}</option>
                                <option value="Pago">{{ __('Pago') }}</option>
                            </select>
                            <input name="comprovante" type="file" accept="image/*,application/pdf" class="w-full text-sm text-slate-600 dark:text-slate-200">
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                    <span id="nx-form-engenharia-lote-submit-label">{{ __('Criar lote') }}</span>
                                </button>
                                <button id="nx-form-engenharia-lote-cancel" type="button" class="hidden rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                    {{ __('Cancelar edição') }}
                                </button>
                            </div>
                            <p id="nx-form-engenharia-lote-error" class="hidden text-sm text-rose-600"></p>
                        </form>

                        <form id="nx-form-engenharia-item" class="grid gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4 dark:border-slate-800 dark:bg-slate-950/30">
                            @csrf
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Novo projeto/serviço') }}</h3>
                            <select id="nx-engenharia-lote-id" name="lote_id" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required></select>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <input name="data_lancamento" type="date" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                                <input name="data_pagamento" type="date" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            </div>
                            <input name="cliente_nome" type="text" placeholder="{{ __('Cliente') }}" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                            <input name="servico_tipo" type="text" placeholder="{{ __('Projeto/serviço') }}" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <input name="receita" type="number" min="0" step="0.01" placeholder="{{ __('Receita') }}" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900" required>
                                <input name="custos_extras" type="number" min="0" step="0.01" value="0" placeholder="{{ __('Custos extras') }}" class="w-full rounded-lg border border-slate-200 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900">
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                    <span id="nx-form-engenharia-item-submit-label">{{ __('Adicionar item') }}</span>
                                </button>
                                <button id="nx-form-engenharia-item-cancel" type="button" class="hidden rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                    {{ __('Cancelar edição') }}
                                </button>
                            </div>
                            <p id="nx-form-engenharia-item-error" class="hidden text-sm text-rose-600"></p>
                        </form>
                    </div>
                @endif

                <div class="mt-4 flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-center lg:justify-between">
                    <input id="nx-filter-engenharia-q" type="search" placeholder="{{ __('Buscar empresa/cliente') }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 sm:max-w-md lg:min-w-[200px] lg:flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <select id="nx-filter-engenharia-sort" title="{{ __('Ordenar por') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <option value="mes_referencia">{{ __('Mês referência') }}</option>
                            <option value="empresa_parceira">{{ __('Cliente') }}</option>
                            <option value="status_pagamento">{{ __('Status') }}</option>
                        </select>
                        <select id="nx-filter-engenharia-dir" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <option value="desc">{{ __('Decrescente') }}</option>
                            <option value="asc">{{ __('Crescente') }}</option>
                        </select>
                        <select id="nx-filter-engenharia-limit" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                        </select>
                    </div>
                </div>
                <div id="nx-engenharia-lista" class="mt-4 space-y-3"></div>
                <div id="nx-pagination-engenharia" class="mt-3"></div>
            </div>
        </div>

        <div data-nx-fin-panel="notas" class="hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Central de Notas') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Pendências e emissões do financeiro') }}</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                    <input id="nx-filter-notas-q" type="search" placeholder="{{ __('Buscar cliente, serviço ou parceiro') }}" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 sm:w-80 sm:min-w-[200px] sm:flex-1">
                    <select id="nx-notas-status" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <option value="todos">{{ __('Todas') }}</option>
                        <option value="false" selected>{{ __('Pendentes') }}</option>
                        <option value="true">{{ __('Emitidas') }}</option>
                    </select>
                    <select id="nx-filter-notas-sort" title="{{ __('Ordenar por') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <option value="data">{{ __('Data') }}</option>
                        <option value="receita">{{ __('Valor') }}</option>
                        <option value="modulo">{{ __('Módulo') }}</option>
                        <option value="cliente">{{ __('Cliente') }}</option>
                        <option value="servico">{{ __('Serviço') }}</option>
                    </select>
                    <select id="nx-filter-notas-dir" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <option value="desc">{{ __('Decrescente') }}</option>
                        <option value="asc">{{ __('Crescente') }}</option>
                    </select>
                    <select id="nx-filter-notas-limit" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-xs uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-200">
                        <tr>
                            <th class="px-4 py-3">{{ __('Data') }}</th>
                            <th class="px-4 py-3">{{ __('Módulo') }}</th>
                            <th class="px-4 py-3">{{ __('Cliente') }}</th>
                            <th class="px-4 py-3">{{ __('Serviço') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Valor') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Ação') }}</th>
                        </tr>
                    </thead>
                    <tbody id="nx-tbody-notas" class="bg-white dark:bg-slate-900"></tbody>
                </table>
            </div>
            <div id="nx-pagination-notas" class="mt-3"></div>
        </div>
    </div>
</x-app-layout>

