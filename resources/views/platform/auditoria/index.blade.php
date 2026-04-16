<x-platform-layout :title="__('Auditoria da plataforma')">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Auditoria da plataforma') }}</h2>
        </div>
    </x-slot>

    <div class="space-y-4">
        <form method="GET" action="{{ route('platform.auditoria.index') }}" class="flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Busca') }}</label>
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('Resumo, ação ou subject…') }}" class="mt-1 min-w-[240px] rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white" />
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Tipo de ação') }}</label>
                <select name="action" class="mt-1 min-w-[min(100%,280px)] rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    <option value="">{{ __('Todas as ações') }}</option>
                    @foreach ($acoes as $act)
                        <option value="{{ $act }}" @selected($action === $act)>{{ $act }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">{{ __('Filtrar') }}</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Quando') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Ação') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Usuário') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Empresa') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Resumo') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($logs as $l)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">{{ $l->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $l->action }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                {{ $l->user?->name ?? '—' }}
                                @if ($l->impersonator)
                                    <span class="ml-2 text-xs text-amber-700 dark:text-amber-400">({{ __('impersonado por') }} {{ $l->impersonator->name }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $l->empresa?->nome ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">{{ $l->summary }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('Sem registos.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $logs->links() }}</div>
    </div>
</x-platform-layout>

