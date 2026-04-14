<x-platform-layout :title="__('Utilizadores')">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Utilizadores') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ __('Lista global: filtre por empresa ou pesquise por nome e e-mail.') }}</p>
            </div>
            <a href="{{ route('platform.usuarios.create', $empresaId > 0 ? ['empresa_id' => $empresaId] : []) }}" class="inline-flex items-center rounded-lg bg-violet-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-violet-500">
                {{ __('Novo utilizador') }}
            </a>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <form method="GET" action="{{ route('platform.usuarios.index') }}" class="flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Busca') }}</label>
                <input type="search" name="q" value="{{ $q }}" placeholder="{{ __('Nome ou e-mail…') }}" class="mt-1 min-w-[240px] rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white" />
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Empresa') }}</label>
                <select name="empresa_id" class="mt-1 min-w-[220px] rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    <option value="0">{{ __('(todas)') }}</option>
                    @foreach ($empresas as $e)
                        <option value="{{ $e->id }}" @selected((int) $empresaId === (int) $e->id)>{{ $e->nome }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-white">{{ __('Filtrar') }}</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Nome') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('E-mail') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Empresa') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Papel') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Data de criação') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Estado') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($usuarios as $u)
                        @php
                            $papeisEmpresa = $u->empresa_id
                                ? $u->roles->where('empresa_id', (int) $u->empresa_id)->pluck('name')->filter()->unique()->values()
                                : collect();
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $u->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $u->email }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $u->empresa?->nome ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                @if ($u->is_platform_admin)
                                    <span class="text-slate-600 dark:text-slate-400">{{ __('Administrador da plataforma') }}</span>
                                @elseif ($papeisEmpresa->isNotEmpty())
                                    {{ $papeisEmpresa->implode(', ') }}
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm tabular-nums text-slate-600 dark:text-slate-300">{{ $u->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                @if ($u->is_disabled)
                                    <span class="text-red-600 dark:text-red-400">{{ __('Bloqueado') }}</span>
                                @elseif ($u->is_platform_admin || $u->empresa_id)
                                    <span class="text-emerald-600 dark:text-emerald-400">{{ __('Ativo') }}</span>
                                @else
                                    <span class="text-amber-700 dark:text-amber-400">{{ __('Inativo') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a href="{{ route('platform.usuarios.edit', $u) }}" class="font-medium text-violet-600 hover:text-violet-500 dark:text-violet-400">{{ __('Editar') }}</a>
                                    @if (auth()->id() !== $u->id)
                                        <form method="POST" action="{{ route('platform.impersonate.start', $u) }}" class="inline" onsubmit="return confirm(@json(__('Entrar como este utilizador? A ação será registada.')));">
                                            @csrf
                                            <button type="submit" class="font-medium text-amber-700 hover:text-amber-600 dark:text-amber-400">{{ __('Impersonar') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('Nenhum utilizador encontrado.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $usuarios->links() }}</div>
    </div>
</x-platform-layout>

