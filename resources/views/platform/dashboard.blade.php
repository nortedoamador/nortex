<x-platform-layout :title="__('Dashboard da plataforma')">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Dashboard da plataforma') }}</h2>
    </x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Empresas') }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $totEmpresas }}</p>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Ativas: :n', ['n' => $totEmpresasAtivas]) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Usuários') }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $totUsuarios }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Admins plataforma') }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900 dark:text-white">{{ $totPlatformAdmins }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Acessos rápidos') }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a class="rounded-lg bg-violet-600 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-violet-500" href="{{ route('platform.empresas.index') }}">{{ __('Empresas') }}</a>
                    <a class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white" href="{{ route('platform.usuarios.index') }}">{{ __('Usuários') }}</a>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Última atividade') }}</h3>
            </div>
            <div class="divide-y divide-slate-200 dark:divide-slate-800">
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
                    <div class="px-5 py-10 text-center text-sm text-slate-500">{{ __('Sem registos ainda.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</x-platform-layout>

