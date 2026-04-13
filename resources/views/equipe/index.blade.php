<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Equipe') }}</h2>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('equipe.logs.export', request()->only('acao')) }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    {{ __('Exportar CSV') }}
                </a>
                <a href="{{ route('equipe.create') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-500">
                    {{ __('Novo usuário') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl space-y-4">
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

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">{{ __('Nome') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">{{ __('E-mail') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">{{ __('Papéis') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach ($usuarios as $u)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">
                                    {{ $u->name }}
                                    @if ($u->id === Auth::id())
                                        <span class="ml-2 text-xs font-normal text-slate-500">({{ __('você') }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $u->email }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($u->roles as $role)
                                            <span class="inline-flex rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-200">{{ $role->name }}</span>
                                        @empty
                                            <span class="text-xs text-amber-600 dark:text-amber-400">{{ __('Sem papel') }}</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                    <div class="flex flex-wrap items-center justify-end gap-x-3 gap-y-1">
                                        <a href="{{ route('equipe.edit', $u) }}" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Editar') }}</a>
                                        @can('delete', $u)
                                            <form
                                                method="POST"
                                                action="{{ route('equipe.destroy', $u) }}"
                                                class="inline"
                                                onsubmit="return confirm(@json(__('Tem certeza? Esta ação não pode ser desfeita.')));"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-red-600 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300">{{ __('Remover') }}</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Registro de alterações na equipe') }}</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Histórico paginado (15 por página). Até 5000 linhas na exportação CSV.') }}</p>
                    </div>
                    <form method="GET" action="{{ route('equipe.index') }}" class="flex flex-wrap items-end gap-2">
                        <div>
                            <label for="filtro_acao_registos" class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('Tipo') }}</label>
                            <select
                                id="filtro_acao_registos"
                                name="acao"
                                class="rounded-lg border border-slate-300 bg-white text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                                onchange="this.form.submit()"
                            >
                                <option value="">{{ __('Todos') }}</option>
                                <option value="user_created" @selected($acaoFiltro === 'user_created')>{{ __('Criação de usuário') }}</option>
                                <option value="user_updated" @selected($acaoFiltro === 'user_updated')>{{ __('Alteração') }}</option>
                                <option value="user_deleted" @selected($acaoFiltro === 'user_deleted')>{{ __('Remoção') }}</option>
                            </select>
                        </div>
                        <noscript>
                            <button type="submit" class="rounded-lg bg-slate-200 px-3 py-2 text-xs font-semibold dark:bg-slate-700">{{ __('Filtrar') }}</button>
                        </noscript>
                    </form>
                </div>
                @if ($logs->isEmpty())
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Nenhum registro ainda.') }}</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($logs as $log)
                            <li class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 text-sm dark:border-slate-800 dark:bg-slate-950/40">
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $log->summary }}</span>
                                    <time class="text-xs tabular-nums text-slate-500 dark:text-slate-400" datetime="{{ $log->created_at?->toIso8601String() }}">
                                        {{ $log->created_at?->format('d/m/Y H:i') }}
                                    </time>
                                </div>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ __('Autor: :nome', ['nome' => $log->actor?->name ?? __('conta removida')]) }}
                                </p>
                                @if ($log->meta)
                                    <details class="mt-2">
                                        <summary class="cursor-pointer text-xs font-medium text-indigo-600 dark:text-indigo-400">{{ __('Detalhes') }}</summary>
                                        <ul class="mt-2 space-y-1 text-xs text-slate-600 dark:text-slate-400">
                                            @isset($log->meta['papeis'])
                                                <li>{{ __('Papéis atribuídos: :lista', ['lista' => implode(', ', $log->meta['papeis'])]) }}</li>
                                            @endisset
                                            @if (! empty($log->meta['convite_por_email']))
                                                <li>{{ __('Criação com convite por e-mail (link para definir senha).') }}</li>
                                            @endif
                                            @isset($log->meta['name'])
                                                <li>{{ __('Nome: :de → :para', ['de' => $log->meta['name']['de'], 'para' => $log->meta['name']['para']]) }}</li>
                                            @endisset
                                            @isset($log->meta['email'])
                                                <li>{{ __('E-mail: :de → :para', ['de' => $log->meta['email']['de'], 'para' => $log->meta['email']['para']]) }}</li>
                                            @endisset
                                            @isset($log->meta['password']['alterada'])
                                                <li>{{ __('Senha alterada.') }}</li>
                                            @endisset
                                            @php
                                                $rolesMeta = $log->meta['roles'] ?? null;
                                            @endphp
                                            @if (is_array($rolesMeta) && (! empty($rolesMeta['anteriores']) || ! empty($rolesMeta['novos'])))
                                                <li>{{ __('Papéis antes: :a', ['a' => implode(', ', $rolesMeta['anteriores'] ?? [__('(nenhum)')])]) }}</li>
                                                <li>{{ __('Papéis depois: :b', ['b' => implode(', ', $rolesMeta['novos'] ?? [__('(nenhum)')])]) }}</li>
                                            @endif
                                            @isset($log->meta['roles_ids'])
                                                <li>{{ __('Papéis (IDs legado): :ids', ['ids' => implode(', ', $log->meta['roles_ids'])]) }}</li>
                                            @endisset
                                            @isset($log->meta['removido'])
                                                <li>{{ __('Conta removida: :nome — :email', ['nome' => $log->meta['removido']['nome'], 'email' => $log->meta['removido']['email']]) }}</li>
                                                @if (! empty($log->meta['removido']['papeis']))
                                                    <li>{{ __('Papéis que tinha: :p', ['p' => implode(', ', $log->meta['removido']['papeis'])]) }}</li>
                                                @endif
                                            @endisset
                                        </ul>
                                    </details>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    @if ($logs->hasPages())
                        <div class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                            {{ $logs->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
