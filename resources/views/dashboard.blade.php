<x-app-layout title="{{ __('Dashboard') }}">
    <div class="mx-auto max-w-[1600px] space-y-8 px-4 py-6 sm:px-6 lg:px-8">
        {{-- Cabeçalho --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    {{ __('Olá, :name', ['name' => Auth::user()->name]) }} 👋
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Aqui está o resumo do seu dia') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <form class="relative hidden min-w-[200px] sm:block" action="#" method="get" onsubmit="return false;">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    </span>
                    <input
                        type="search"
                        name="q"
                        placeholder="{{ __('Busca…') }}"
                        class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                    />
                </form>
                <button
                    type="button"
                    class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                    title="{{ __('Notificações') }}"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                    <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-slate-900"></span>
                </button>
                <a
                    href="{{ route('profile.edit') }}"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-semibold text-white shadow-md ring-2 ring-white dark:ring-slate-950"
                >
                    {{ strtoupper(\Illuminate\Support\Str::substr(Auth::user()->name, 0, 1)) }}
                </a>
            </div>
        </div>

        @if (! empty($metricasDashboard))
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @php
                    $cards = [
                        ['key' => 'processos_ativos', 'label' => __('Processos Ativos'), 'trend' => '+'.((int) ($metricasDashboard['processos_ativos_semana'] ?? 0)).' '.__('esta semana'), 'trendUp' => true, 'tone' => 'indigo'],
                        ['key' => 'clientes_total', 'label' => __('Clientes'), 'trend' => '+'.((int) ($metricasDashboard['clientes_mes'] ?? 0)).' '.__('este mês'), 'trendUp' => true, 'tone' => 'emerald'],
                        ['key' => 'embarcacoes_total', 'label' => __('Embarcações'), 'trend' => '', 'trendUp' => true, 'tone' => 'amber'],
                    ];
                @endphp
                @foreach ($cards as $card)
                    @php
                        $val = $metricasDashboard[$card['key']] ?? 0;
                        $tone = $card['tone'];
                        $border = match ($tone) {
                            'indigo' => 'border-indigo-100 dark:border-indigo-900/40',
                            'emerald' => 'border-emerald-100 dark:border-emerald-900/40',
                            'amber' => 'border-amber-100 dark:border-amber-900/40',
                            default => 'border-slate-200 dark:border-slate-700',
                        };
                        $accent = match ($tone) {
                            'indigo' => 'text-indigo-600 dark:text-indigo-400',
                            'emerald' => 'text-emerald-600 dark:text-emerald-400',
                            'amber' => 'text-amber-600 dark:text-amber-400',
                            default => 'text-slate-600',
                        };
                    @endphp
                    <div class="rounded-2xl border bg-white p-5 shadow-sm dark:bg-slate-900 {{ $border }}">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-3xl font-bold tabular-nums text-slate-900 dark:text-white">{{ $val }}</p>
                                <p class="mt-1 text-sm font-medium text-slate-600 dark:text-slate-300">{{ $card['label'] }}</p>
                            </div>
                            <span class="inline-flex rounded-lg px-2 py-0.5 text-xs font-semibold {{ $card['trendUp'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-amber-50 text-amber-800 dark:bg-amber-950/50 dark:text-amber-200' }}">
                                {{ $card['trend'] }}
                            </span>
                        </div>
                        <svg class="mt-4 h-10 w-full {{ $accent }}" preserveAspectRatio="none" viewBox="0 0 100 28" aria-hidden="true">
                            <path fill="currentColor" fill-opacity="0.12" d="M0,22 Q20,10 40,16 T80,12 L100,8 V28 H0Z" />
                            <path fill="none" stroke="currentColor" stroke-width="1.25" d="M0,22 Q20,10 40,16 T80,12 L100,8" />
                        </svg>
                    </div>
                @endforeach
                <div class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm dark:border-violet-900/40 dark:bg-slate-900">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-2xl font-bold tabular-nums text-slate-900 dark:text-white">R$ —</p>
                            <p class="mt-1 text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('Faturamento mensal') }}</p>
                        </div>
                        <span class="inline-flex rounded-lg bg-violet-50 px-2 py-0.5 text-xs font-semibold text-violet-700 dark:bg-violet-950/50 dark:text-violet-300">+22%</span>
                    </div>
                    <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">{{ __('Integração financeira em desenvolvimento.') }}</p>
                    <svg class="mt-2 h-10 w-full text-violet-600 dark:text-violet-400" preserveAspectRatio="none" viewBox="0 0 100 28" aria-hidden="true">
                        <path fill="currentColor" fill-opacity="0.12" d="M0,18 Q25,24 50,12 T100,6 V28 H0Z" />
                        <path fill="none" stroke="currentColor" stroke-width="1.25" d="M0,18 Q25,24 50,12 T100,6" />
                    </svg>
                </div>
            </div>
        @endif

        @isset($alertasResumo)
            @php
                $nxAr = $alertasResumo;
                $nxHrefExigencia = route('processos.index', ['status' => \App\Enums\ProcessoStatus::EmExigencia->value]);
                $nxHrefHab = Auth::user()->hasPermission('habilitacoes.view')
                    ? route('habilitacoes.index')
                    : route('clientes.index');
                $nxHrefTie = route('embarcacoes.index');
                $nxHrefPendencias = route('processos.index');
            @endphp
            <section class="rounded-2xl border border-amber-200/80 bg-amber-50/50 p-5 shadow-sm dark:border-amber-900/40 dark:bg-amber-950/20 sm:p-6">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-200/80 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200" aria-hidden="true">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" /></svg>
                        </span>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Alertas operacionais') }}</h2>
                        </div>
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <a href="{{ $nxHrefExigencia }}" class="group flex min-h-[5.5rem] overflow-hidden rounded-xl border border-amber-100/90 bg-white/95 shadow-sm transition hover:border-amber-200 hover:shadow dark:border-slate-700/80 dark:bg-slate-900/80 dark:hover:border-amber-900/60">
                        <span class="w-1.5 shrink-0 bg-[#F2994A]" aria-hidden="true"></span>
                        <div class="flex min-w-0 flex-1 flex-col justify-center px-4 py-3">
                            <p class="text-sm font-bold leading-snug text-slate-900 dark:text-white">
                                {{ trans_choice(':count processo em exigência|:count processos em exigência', $nxAr['em_exigencia'], ['count' => $nxAr['em_exigencia']]) }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Necessitam ação imediata') }}</p>
                        </div>
                    </a>
                    <a href="{{ $nxHrefHab }}" class="group flex min-h-[5.5rem] overflow-hidden rounded-xl border border-amber-100/90 bg-white/95 shadow-sm transition hover:border-amber-200 hover:shadow dark:border-slate-700/80 dark:bg-slate-900/80 dark:hover:border-amber-900/60">
                        <span class="w-1.5 shrink-0 bg-red-500" aria-hidden="true"></span>
                        <div class="flex min-w-0 flex-1 flex-col justify-center px-4 py-3">
                            <p class="text-sm font-bold leading-snug text-slate-900 dark:text-white">
                                {{ trans_choice(':count habilitação a vencer|:count habilitações a vencer', $nxAr['habilitacoes_vencendo'], ['count' => $nxAr['habilitacoes_vencendo']]) }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Próximos 30 dias') }}</p>
                            @if (($nxAr['habilitacoes_vencidas'] ?? 0) > 0)
                                <p class="mt-1 text-xs font-semibold text-red-600 dark:text-red-400">
                                    {{ trans_choice(':count CHA vencida|:count CHAs vencidas', $nxAr['habilitacoes_vencidas'], ['count' => $nxAr['habilitacoes_vencidas']]) }}
                                </p>
                            @endif
                        </div>
                    </a>
                    <a href="{{ $nxHrefTie }}" class="group flex min-h-[5.5rem] overflow-hidden rounded-xl border border-amber-100/90 bg-white/95 shadow-sm transition hover:border-amber-200 hover:shadow dark:border-slate-700/80 dark:bg-slate-900/80 dark:hover:border-amber-900/60">
                        <span class="w-1.5 shrink-0 bg-red-500" aria-hidden="true"></span>
                        <div class="flex min-w-0 flex-1 flex-col justify-center px-4 py-3">
                            <p class="text-sm font-bold leading-snug text-slate-900 dark:text-white">
                                {{ trans_choice(':count TIE a vencer|:count TIEs a vencer', $nxAr['tie_vencendo'], ['count' => $nxAr['tie_vencendo']]) }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Próximos 30 dias') }}</p>
                        </div>
                    </a>
                    <a href="{{ $nxHrefPendencias }}" class="group flex min-h-[5.5rem] overflow-hidden rounded-xl border border-amber-100/90 bg-white/95 shadow-sm transition hover:border-amber-200 hover:shadow dark:border-slate-700/80 dark:bg-slate-900/80 dark:hover:border-amber-900/60">
                        <span class="w-1.5 shrink-0 bg-indigo-500" aria-hidden="true"></span>
                        <div class="flex min-w-0 flex-1 flex-col justify-center px-4 py-3">
                            <p class="text-sm font-bold leading-snug text-slate-900 dark:text-white">
                                {{ trans_choice(':count processo com documentos pendentes|:count processos com documentos pendentes', $nxAr['docs_pendentes'], ['count' => $nxAr['docs_pendentes']]) }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Checklist incompleto') }}</p>
                        </div>
                    </a>
                </div>
            </section>
        @endisset

        @isset($kanban)
            @if (! empty($metricasDashboard))
                <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200" aria-hidden="true">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 14l3-3 4 4 6-7" /></svg>
                            </span>
                            <h2 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Processos por Status') }}</h2>
                        </div>
                        <a href="{{ route('processos.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Ver lista') }}</a>
                    </div>
                    <div class="flex flex-wrap gap-3 lg:flex-nowrap">
                        @php
                            $nxStatusCards = [
                                ['key' => 'em_montagem', 'label' => __('Em montagem'), 'dot' => 'bg-[#F2C94C]', 'href' => route('processos.index', ['status' => \App\Enums\ProcessoStatus::EmMontagem->value])],
                                ['key' => 'a_protocolar', 'label' => __('A protocolar'), 'dot' => 'bg-[#9B51E0]', 'href' => route('processos.index', ['status' => \App\Enums\ProcessoStatus::AProtocolar->value])],
                                ['key' => 'protocolado', 'label' => __('Protocolado'), 'dot' => 'bg-[#2F80ED]', 'href' => route('processos.index', ['status' => \App\Enums\ProcessoStatus::Protocolado->value])],
                                ['key' => 'em_exigencia', 'label' => __('Em exigência'), 'dot' => 'bg-[#F2994A]', 'href' => route('processos.index', ['status' => \App\Enums\ProcessoStatus::EmExigencia->value])],
                                ['key' => 'concluido', 'label' => __('Concluído'), 'dot' => 'bg-[#6FCF97]', 'href' => route('processos.index', ['status' => \App\Enums\ProcessoStatus::Concluido->value])],
                            ];
                        @endphp
                        @foreach ($nxStatusCards as $c)
                            @php $val = (int) ($metricasDashboard[$c['key']] ?? 0); @endphp
                            <a href="{{ $c['href'] }}" class="group min-w-[140px] flex-1 basis-0 rounded-xl border border-slate-200/80 bg-white p-4 text-center shadow-sm transition hover:border-indigo-200 hover:shadow dark:border-slate-800 dark:bg-slate-950/30 dark:hover:border-indigo-900/50">
                                <div class="mx-auto mb-2 h-1.5 w-8 rounded-full {{ $c['dot'] }}" aria-hidden="true"></div>
                                <div class="text-2xl font-bold tabular-nums text-slate-900 dark:text-white">{{ $val }}</div>
                                <div class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">{{ $c['label'] }}</div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Pipeline de processos') }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Visão geral do fluxo') }}</p>
                    </div>
                    <a href="{{ route('processos.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Ver lista') }}</a>
                </div>
                @include('processos.partials.kanban-board', [
                    'colunas' => $kanban['colunas'],
                    'processos' => $kanban['processos'],
                    'podeMoverKanban' => $kanban['podeMoverKanban'],
                ])
            </section>
        @else
            <div class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-slate-600 dark:text-slate-300">{{ __('Você não tem permissão para visualizar processos. Use o menu para acessar outros módulos.') }}</p>
            </div>
        @endisset

        <div>
            <h2 class="mb-4 text-base font-semibold text-slate-900 dark:text-white">{{ __('Ações rápidas') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @can('create', \App\Models\Processo::class)
                    <a href="{{ route('processos.index', ['abrir_novo' => 1]) }}" class="group flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-900/50">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        </span>
                        <div class="min-w-0 text-left">
                            <p class="font-semibold text-slate-900 dark:text-white">{{ __('Novo processo') }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('CHA, embarcação e CIR — NORMAM 211') }}</p>
                        </div>
                    </a>
                @endcan
                @if (Auth::user()->hasPermission('processos.view'))
                    <a href="{{ route('processos.index') }}" class="group flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:border-violet-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-violet-900/50">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-950/60 dark:text-violet-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" /></svg>
                        </span>
                        <div class="min-w-0 text-left">
                            <p class="font-semibold text-slate-900 dark:text-white">{{ __('Lista de processos') }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Buscar, filtrar e alterar etapa') }}</p>
                        </div>
                    </a>
                @endif
                <div class="flex cursor-not-allowed items-center gap-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-5 opacity-70 dark:border-slate-700 dark:bg-slate-900/50" title="{{ __('Em breve') }}">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    </span>
                    <div class="min-w-0 text-left">
                        <p class="font-semibold text-slate-700 dark:text-slate-300">{{ __('Gerar contrato') }}</p>
                        <p class="text-xs text-slate-500">{{ __('Em breve') }}</p>
                    </div>
                </div>
                @if (Auth::user()->hasPermission('clientes.manage'))
                    <a href="{{ route('clientes.create') }}" class="group flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:border-amber-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-amber-900/50">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        </span>
                        <div class="min-w-0 text-left">
                            <p class="font-semibold text-slate-900 dark:text-white">{{ __('Novo cliente') }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Abrir formulário de cadastro') }}</p>
                        </div>
                    </a>
                @elseif (Auth::user()->hasPermission('clientes.view'))
                    <a href="{{ route('clientes.index') }}" class="group flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:border-amber-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-amber-900/50">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        </span>
                        <div class="min-w-0 text-left">
                            <p class="font-semibold text-slate-900 dark:text-white">{{ __('Clientes') }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Ver lista') }}</p>
                        </div>
                    </a>
                @endif
            </div>
        </div>

        <div class="grid gap-6 pb-8 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white">
                        <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                        {{ __('Agenda') }}
                    </h3>
                    @if (Auth::user()->hasPermission('empresa.manage'))
                        <a href="{{ route('admin.empresa.compromissos.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Gerir compromissos') }}</a>
                    @endif
                </div>
                <p class="mb-3 text-xs text-slate-500 dark:text-slate-400">{{ __('Reuniões, atendimento na Marinha e aulas náuticas agendadas.') }}</p>
                <ul class="space-y-3 text-sm">
                    @forelse (($agendaItens ?? []) as $item)
                        @php
                            $tone = $item['badge_tone'] ?? 'slate';
                            $badgeClass = match ($tone) {
                                'violet' => 'bg-violet-100 text-violet-800 dark:bg-violet-950/50 dark:text-violet-200',
                                'emerald' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200',
                                default => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/50 dark:text-indigo-200',
                            };
                        @endphp
                        <li class="rounded-xl border border-slate-100 bg-slate-50/80 p-3 dark:border-slate-800 dark:bg-slate-950/40">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $badgeClass }}">{{ $item['badge'] }}</span>
                                @if (! empty($item['href']))
                                    <a href="{{ $item['href'] }}" class="ml-auto text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Abrir') }}</a>
                                @endif
                            </div>
                            <p class="mt-1.5 font-medium text-slate-800 dark:text-slate-200">{{ $item['titulo'] }}</p>
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ $item['meta'] }}</p>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-4 text-center text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-950/30 dark:text-slate-400">
                            {{ __('Nenhum compromisso ou aula futura.') }}
                            @if (Auth::user()->hasPermission('empresa.manage'))
                                <a href="{{ route('admin.empresa.compromissos.create') }}" class="mt-2 block font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Adicionar reunião ou dia na Marinha') }}</a>
                            @endif
                        </li>
                    @endforelse
                </ul>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white">
                        <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                        {{ __('Provas na Marinha') }}
                    </h3>
                    @if (Auth::user()->hasPermission('processos.view'))
                        <a href="{{ route('processos.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Ver processos') }}</a>
                    @endif
                </div>
                <p class="mb-3 text-xs text-slate-500 dark:text-slate-400">{{ __('Processos em «Aguardando prova» com a data indicada na ficha.') }}</p>
                <ul class="space-y-3 text-sm">
                    @if (! Auth::user()->hasPermission('processos.view'))
                        <li class="rounded-xl border border-slate-100 bg-slate-50/80 p-3 text-xs text-slate-500 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-400">
                            {{ __('Não tem permissão para ver a lista de processos.') }}
                        </li>
                    @else
                        @forelse (($provasMarinhaItens ?? []) as $pv)
                            <li class="rounded-xl border border-slate-100 bg-slate-50/80 p-3 dark:border-slate-800 dark:bg-slate-950/40">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if (! empty($pv['sem_data']))
                                        <span class="inline-flex rounded-md bg-slate-200/90 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-700 dark:bg-slate-700 dark:text-slate-200">{{ __('Sem data') }}</span>
                                    @elseif (! empty($pv['atrasado']))
                                        <span class="inline-flex rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-900 dark:bg-amber-950/50 dark:text-amber-200">{{ __('Data passada') }}</span>
                                    @else
                                        <span class="inline-flex rounded-md bg-sky-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-sky-900 dark:bg-sky-950/50 dark:text-sky-200">{{ __('Agendada') }}</span>
                                    @endif
                                    <a href="{{ $pv['href'] }}" class="ml-auto text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Ficha') }}</a>
                                </div>
                                <p class="mt-1.5 font-medium text-slate-800 dark:text-slate-200">{{ $pv['titulo'] }}</p>
                                <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ $pv['meta'] }}</p>
                            </li>
                        @empty
                            <li class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-4 text-center text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-950/30 dark:text-slate-400">
                                {{ __('Nenhum processo em «Aguardando prova».') }}
                            </li>
                        @endforelse
                    @endif
                </ul>
            </div>
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-900 dark:text-white">
                    <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                    {{ __('Atividade recente') }}
                </h3>
                <ul class="space-y-3 text-sm">
                    <li class="rounded-xl border border-slate-100 bg-slate-50/80 p-3 dark:border-slate-800 dark:bg-slate-950/40">
                        <p class="font-medium text-slate-800 dark:text-slate-200">{{ __('Histórico da sua conta') }}</p>
                        <p class="mt-1 text-xs font-medium text-amber-700 dark:text-amber-400">{{ __('Em breve') }} <span class="font-normal text-slate-500 dark:text-slate-400">· {{ __('últimas ações e notificações') }}</span></p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
