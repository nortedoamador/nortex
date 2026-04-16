<x-platform-layout :title="__('Visão geral da plataforma')">
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Visão geral da plataforma') }}</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('Dados filtrados por :estado', ['estado' => $selectedUfName]) }}</p>
        </div>
    </x-slot>

    <div class="space-y-5">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200/80 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-500/30 dark:bg-emerald-950/40 dark:text-emerald-100" role="status">
                {{ session('status') }}
            </div>
        @endif
        <div class="grid gap-5 xl:items-start xl:grid-cols-[minmax(0,1fr)_260px]">
            <section class="nx-platform-card overflow-hidden xl:max-w-[780px]">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200/80 px-5 py-3.5 dark:border-slate-800">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Mapa de empresas') }}</p>
                        <p id="platformMapSubtitle" class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Clique num estado para filtrar') }}</p>
                    </div>
                    @if ($selectedUf)
                        <a
                            href="{{ route('platform.dashboard') }}"
                            class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-violet-200 hover:text-violet-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-violet-500/50 dark:hover:text-violet-300"
                        >
                            {{ __('Limpar filtro') }}
                        </a>
                    @endif
                </div>

                <div class="p-4">
                    <form id="platformDashboardFilterForm" method="GET" action="{{ route('platform.dashboard') }}" class="hidden">
                        <input id="platformDashboardUfInput" type="hidden" name="uf" value="{{ $selectedUf }}">
                    </form>

                    <div class="nx-platform-map-shell rounded-[28px] border border-slate-100 bg-[radial-gradient(circle_at_top,_rgba(129,140,248,0.18),_rgba(255,255,255,0)_48%),linear-gradient(180deg,#ffffff_0%,#f8f9ff_100%)] p-4 shadow-[0_18px_45px_rgba(99,102,241,0.08)] dark:border-slate-800 dark:bg-[radial-gradient(circle_at_top,_rgba(99,102,241,0.16),_rgba(15,23,42,0)_48%),linear-gradient(180deg,#0f172a_0%,#111827_100%)]">
                        <div
                            id="platformBrazilMap"
                            class="nx-platform-map"
                            data-platform-brazil-map="1"
                            data-map-counts='@json($mapCounts)'
                            data-map-stats='@json($mapStats)'
                            data-selected-uf="{{ $selectedUf }}"
                            data-form-id="platformDashboardFilterForm"
                            data-input-id="platformDashboardUfInput"
                            data-subtitle-id="platformMapSubtitle"
                        ></div>
                        <div
                            id="platformMapLegend"
                            class="nx-platform-map-legend is-hidden"
                            aria-live="polite"
                        >
                            <p id="platformMapLegendTitle" class="nx-platform-map-legend-title"></p>
                            <p id="platformMapLegendMeta" class="nx-platform-map-legend-meta"></p>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-[11px] text-slate-500 dark:text-slate-400">
                        <span class="font-semibold uppercase tracking-[0.22em] text-slate-400 dark:text-slate-500">{{ __('Densidade') }}</span>
                        <span class="inline-flex items-center gap-2"><span class="nx-platform-legend" style="background:#f2f1ff"></span>0</span>
                        <span class="inline-flex items-center gap-2"><span class="nx-platform-legend" style="background:#e3defe"></span>1</span>
                        <span class="inline-flex items-center gap-2"><span class="nx-platform-legend" style="background:#c7bbfd"></span>2-3</span>
                        <span class="inline-flex items-center gap-2"><span class="nx-platform-legend" style="background:#a38cf9"></span>4-6</span>
                        <span class="inline-flex items-center gap-2"><span class="nx-platform-legend" style="background:#5b3df5"></span>7+</span>
                    </div>

                    <div class="mt-6 border-t border-slate-200/80 pt-5 dark:border-slate-800">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">{{ __('Empresas (dados reais)') }}</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-500">{{ __('UF pela empresa ou, se vazio, inferida pelo maior volume de clientes no estado.') }}</p>
                        @if ($empresasLista->isEmpty())
                            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">{{ __('Nenhuma empresa neste filtro.') }}</p>
                        @else
                            <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200/80 dark:border-slate-800">
                                <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-800">
                                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                                        <tr>
                                            <th class="px-3 py-2.5">{{ __('Empresa') }}</th>
                                            <th class="px-3 py-2.5">{{ __('UF') }}</th>
                                            <th class="px-3 py-2.5">{{ __('Estado') }}</th>
                                            <th class="px-3 py-2.5">{{ __('Utilizadores') }}</th>
                                            <th class="px-3 py-2.5">{{ __('Situação') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        @foreach ($empresasLista as $row)
                                            <tr class="bg-white dark:bg-slate-900/40">
                                                <td class="px-3 py-2.5 font-medium text-slate-900 dark:text-white">
                                                    <a href="{{ route('platform.empresas.show', $row['id']) }}" class="text-violet-700 hover:underline dark:text-violet-300">{{ $row['nome'] }}</a>
                                                </td>
                                                <td class="px-3 py-2.5 font-mono text-slate-700 dark:text-slate-200">{{ $row['uf'] ?? '—' }}</td>
                                                <td class="px-3 py-2.5 text-slate-600 dark:text-slate-300">{{ $row['uf_label'] }}</td>
                                                <td class="px-3 py-2.5 text-slate-700 dark:text-slate-200">{{ $row['usuarios'] }}</td>
                                                <td class="px-3 py-2.5">
                                                    @if ($row['ativa'])
                                                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200">{{ __('Ativa') }}</span>
                                                    @else
                                                        <span class="inline-flex rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">{{ __('Inativa') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <aside class="space-y-4">
                <article class="nx-platform-card border-amber-200/80 bg-amber-50/90 p-4 dark:border-amber-500/25 dark:bg-amber-950/35">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-500/15 text-amber-700 dark:text-amber-300" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655-5.653a2.548 2.548 0 0 1-.16-2.654 2.548 2.548 0 0 1 3.621-1.116l.024.012 5.877 5.877a2.548 2.548 0 0 1 1.116 3.621 2.548 2.548 0 0 1-2.654.16l-3.03-2.496Z" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Manutenção da plataforma') }}</p>
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">
                                @if ($platformMaintenanceEnabled)
                                    {{ __('A plataforma está em modo de manutenção para utilizadores. Apenas administradores da plataforma acedem normalmente.') }}
                                @else
                                    {{ __('Ative para bloquear o acesso dos utilizadores durante atualizações ou reparos. Os administradores da plataforma mantêm acesso.') }}
                                @endif
                            </p>
                            <form method="POST" action="{{ route('platform.maintenance.update') }}" class="mt-4">
                                @csrf
                                @if ($platformMaintenanceEnabled)
                                    <input type="hidden" name="enabled" value="0" />
                                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100">
                                        {{ __('Desativar manutenção') }}
                                    </button>
                                @else
                                    <input type="hidden" name="enabled" value="1" />
                                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-500" onclick="return confirm(@json(__('Colocar a plataforma em manutenção? Os utilizadores deixarão de aceder ao sistema até desativar.')))">
                                        {{ __('Ativar manutenção') }}
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                </article>

                <article class="nx-platform-metric-card">
                    <div>
                        <p class="nx-platform-metric-label">{{ __('Empresas') }}</p>
                        <p class="nx-platform-metric-value">{{ $totEmpresas }}</p>
                        <p class="nx-platform-metric-meta">{{ __('Ativas: :n', ['n' => $totEmpresasAtivas]) }}</p>
                    </div>
                    <div class="nx-platform-metric-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 20.25h16.5M6.75 20.25V7.5a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 .75.75v12.75m1.5 0V4.5a.75.75 0 0 1 .75-.75H18a.75.75 0 0 1 .75.75v15.75" />
                        </svg>
                    </div>
                </article>

                <article class="nx-platform-metric-card">
                    <div>
                        <p class="nx-platform-metric-label">{{ __('Utilizadores') }}</p>
                        <p class="nx-platform-metric-value">{{ $totUsuarios }}</p>
                    </div>
                    <div class="nx-platform-metric-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0-3-.503 9.38 9.38 0 0 0-3 .503M6.75 8.625a5.25 5.25 0 1 1 10.5 0 5.25 5.25 0 0 1-10.5 0ZM3.75 19.5a8.25 8.25 0 0 1 16.5 0" />
                        </svg>
                    </div>
                </article>

                <article class="nx-platform-metric-card">
                    <div>
                        <p class="nx-platform-metric-label">{{ __('Admins plataforma') }}</p>
                        <p class="nx-platform-metric-value">{{ $totPlatformAdmins }}</p>
                    </div>
                    <div class="nx-platform-metric-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-1.5 0h12a1.5 1.5 0 0 1 1.5 1.5v7.5a1.5 1.5 0 0 1-1.5 1.5h-12A1.5 1.5 0 0 1 4.5 19.5V12a1.5 1.5 0 0 1 1.5-1.5Z" />
                        </svg>
                    </div>
                </article>

                <article class="nx-platform-card p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">{{ __('Atalhos') }}</p>
                    <div class="mt-3 space-y-2">
                        <a href="{{ route('platform.empresas.index') }}" class="nx-platform-shortcut nx-platform-shortcut-primary">{{ __('Lista de empresas') }}</a>
                        <a href="{{ route('platform.usuarios.index') }}" class="nx-platform-shortcut">{{ __('Todos os utilizadores') }}</a>
                    </div>
                </article>
            </aside>
        </div>

        <section class="nx-platform-card overflow-hidden">
            <div class="border-b border-slate-200/80 px-5 py-4 dark:border-slate-800">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Última atividade') }}</h3>
            </div>
            <div class="divide-y divide-slate-200/80 dark:divide-slate-800">
                @forelse ($ultimosLogs as $l)
                    <div class="px-5 py-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-sm text-slate-900 dark:text-slate-100">{{ $l->summary }}</p>
                            <p class="text-xs text-slate-500">{{ $l->created_at?->format('Y-m-d H:i') }}</p>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            <span class="font-mono">{{ $l->action }}</span>
                            <span class="mx-1">·</span>
                            {{ $l->user?->name ?? '—' }}
                            @if ($l->empresa)
                                <span class="mx-1">·</span>
                                {{ $l->empresa->nome }}
                            @endif
                        </p>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center text-sm text-slate-500">{{ __('Sem registos ainda para este filtro.') }}</div>
                @endforelse
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mapElement = document.getElementById('platformBrazilMap');
            const shellElement = mapElement?.closest('.nx-platform-map-shell');
            const MAP_RATIO = 465 / 460;
            const MAX_MAP_HEIGHT = 400;
            const legend = document.getElementById('platformMapLegend');
            const legendTitle = document.getElementById('platformMapLegendTitle');
            const legendMeta = document.getElementById('platformMapLegendMeta');

            const applyMapSize = () => {
                if (!mapElement || !shellElement) {
                    return;
                }

                const shellStyles = window.getComputedStyle(shellElement);
                const shellPaddingX =
                    parseFloat(shellStyles.paddingLeft || '0') +
                    parseFloat(shellStyles.paddingRight || '0');
                const availableWidth = Math.max(shellElement.clientWidth - shellPaddingX, 0);
                const widthFromHeight = MAX_MAP_HEIGHT / MAP_RATIO;
                const finalWidth = Math.min(availableWidth, widthFromHeight);
                const finalHeight = Math.min(finalWidth * MAP_RATIO, MAX_MAP_HEIGHT);
                const svg = mapElement.querySelector('#brmap');

                mapElement.style.display = 'block';
                mapElement.style.position = 'relative';
                mapElement.style.width = `${Math.round(finalWidth)}px`;
                mapElement.style.maxWidth = '100%';
                mapElement.style.height = `${Math.round(finalHeight)}px`;
                mapElement.style.maxHeight = `${MAX_MAP_HEIGHT}px`;
                mapElement.style.paddingBottom = '0';
                mapElement.style.marginInline = 'auto';

                if (svg) {
                    svg.style.position = 'static';
                    svg.style.width = '100%';
                    svg.style.height = '100%';
                }
            };

            requestAnimationFrame(() => {
                applyMapSize();
            });

            window.addEventListener('resize', applyMapSize);
        });
    </script>
</x-platform-layout>

