<x-tenant-admin-layout title="{{ __('Auditoria') }}">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Auditoria') }}</h2>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl space-y-4">
            @if ($acoes->isNotEmpty())
                <div class="flex flex-wrap gap-2 text-xs">
                    <a href="{{ tenant_admin_route('auditoria.index') }}" class="rounded-full px-3 py-1 font-semibold {{ $acao === null ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' }}">{{ __('Todas') }}</a>
                    @foreach ($acoes as $act => $count)
                        <a href="{{ tenant_admin_route('auditoria.index', ['acao' => $act]) }}" class="rounded-full px-3 py-1 font-semibold {{ $acao === $act ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' }}">{{ $act }} ({{ $count }})</a>
                    @endforeach
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Quando') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Ação') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Utilizador') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Resumo') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse ($logs as $log)
                            <tr class="align-top hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600 dark:text-slate-300">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 text-xs font-mono text-slate-700 dark:text-slate-300">{{ $log->action }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $log->user?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-200">{{ $log->summary }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('Sem registos.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $logs->links() }}</div>
        </div>
    </div>
</x-tenant-admin-layout>
