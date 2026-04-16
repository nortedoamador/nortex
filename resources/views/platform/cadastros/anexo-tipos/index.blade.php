<x-platform-layout :title="__('Tipos de anexo (upload)')">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Tipos de anexo (upload)') }}</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Regras globais de upload. «Ficheiros na base» são contagens reais; «Contexto» indica em que módulos marcou o tipo (clientes, embarcações, habilitações).') }}</p>
            </div>
            <a href="{{ route('platform.cadastros.anexo-tipos.create') }}" class="inline-flex items-center rounded-lg bg-violet-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-violet-500">
                {{ __('Novo tipo') }}
            </a>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <form method="GET" action="{{ route('platform.cadastros.anexo-tipos.index') }}" class="flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Busca') }}</label>
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('Nome ou slug…') }}" class="mt-1 min-w-[240px] rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white" />
            </div>
            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">{{ __('Filtrar') }}</button>
        </form>

        <div class="overflow-x-auto overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Nome') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Slug') }}</th>
                        <th class="min-w-[200px] px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Ficheiros na base') }}</th>
                        <th class="min-w-[220px] px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Contexto (módulos)') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Max (MB)') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Estado') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-600 dark:text-slate-400"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($tipos as $t)
                        @php
                            $u = $usosPorTipo[$t->id] ?? ['cliente' => 0, 'embarcacao' => 0, 'habilitacao' => 0];
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $t->nome }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $t->slug }}</td>
                            <td class="px-4 py-3 text-xs leading-snug text-slate-700 dark:text-slate-300">
                                <ul class="space-y-0.5">
                                    <li><span class="text-slate-500 dark:text-slate-400">{{ __('Clientes') }}:</span> <span class="tabular-nums font-semibold text-slate-900 dark:text-slate-100">{{ $u['cliente'] }}</span></li>
                                    <li><span class="text-slate-500 dark:text-slate-400">{{ __('Embarcações') }}:</span> <span class="tabular-nums font-semibold text-slate-900 dark:text-slate-100">{{ $u['embarcacao'] }}</span></li>
                                    <li><span class="text-slate-500 dark:text-slate-400">{{ __('Habilitações') }}:</span> <span class="tabular-nums font-semibold text-slate-900 dark:text-slate-100">{{ $u['habilitacao'] }}</span></li>
                                </ul>
                            </td>
                            <td class="max-w-xs px-4 py-3 text-xs text-slate-600 dark:text-slate-400">
                                @php
                                    $fromUsage = \App\Support\PlatformAnexoTipoContextoModulos::keysFromUsoCounts($u);
                                    $mergedCtx = array_values(array_unique(array_merge(
                                        is_array($t->contexto_modulos) ? $t->contexto_modulos : [],
                                        $fromUsage
                                    )));
                                    $ctx = \App\Support\PlatformAnexoTipoContextoModulos::resumo($mergedCtx === [] ? null : $mergedCtx);
                                @endphp
                                @if ($ctx !== '—')
                                    <p class="line-clamp-4" title="{{ $ctx }}">{{ $ctx }}</p>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm tabular-nums text-slate-700 dark:text-slate-300">{{ $t->max_size_mb }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if ($t->ativo)
                                    <span class="text-emerald-600 dark:text-emerald-400">{{ __('Ativo') }}</span>
                                @else
                                    <span class="text-red-600 dark:text-red-400">{{ __('Inativo') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('platform.cadastros.anexo-tipos.edit', $t) }}" class="font-medium text-violet-600 hover:text-violet-500 dark:text-violet-400">{{ __('Editar') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('Nenhum tipo encontrado.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-platform-layout>

