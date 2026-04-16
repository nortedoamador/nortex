<x-platform-layout :title="__('Documentos automatizados (global)')">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Documentos automatizados (global)') }}</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Esqueletos Blade replicados nas empresas; cada empresa pode personalizar.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('platform.cadastros.documentos-automatizados.laboratorio') }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-800 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800">
                    {{ __('Laboratório') }}
                </a>
                <a href="{{ route('platform.cadastros.documentos-automatizados.create') }}" class="inline-flex items-center rounded-lg bg-violet-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-violet-500">
                    {{ __('Novo') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->has('delete'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">
                {{ $errors->first('delete') }}
            </div>
        @endif

        <form method="GET" action="{{ route('platform.cadastros.documentos-automatizados.index') }}" class="flex flex-wrap items-end gap-2">
            <input type="hidden" name="sort" value="{{ $sort }}" />
            <input type="hidden" name="dir" value="{{ $dir }}" />
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Busca') }}</label>
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('Título, slug ou referência…') }}" class="mt-1 min-w-[240px] rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white" />
            </div>
            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">{{ __('Filtrar') }}</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        @include('platform.cadastros.documentos-automatizados.partials.sort-th', ['column' => 'titulo', 'label' => __('Título')])
                        @include('platform.cadastros.documentos-automatizados.partials.sort-th', ['column' => 'slug', 'label' => __('Slug')])
                        @include('platform.cadastros.documentos-automatizados.partials.sort-th', ['column' => 'referencia', 'label' => __('Referência')])
                        @include('platform.cadastros.documentos-automatizados.partials.sort-th', ['column' => 'updated_at', 'label' => __('Atualizado')])
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-600 dark:text-slate-400"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($modelos as $m)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $m->titulo }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $m->slug }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $m->referencia ?: '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-600 dark:text-slate-300">
                                @if ($m->updated_at)
                                    {{ $m->updated_at->format('d/m/y H:i') }}
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('platform.cadastros.documentos-automatizados.edit', $m) }}" class="font-medium text-violet-600 hover:text-violet-500 dark:text-violet-400">{{ __('Editar') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('Nenhum documento encontrado.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-platform-layout>
