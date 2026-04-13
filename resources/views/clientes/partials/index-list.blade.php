<ul class="space-y-3">
    @forelse ($clientes as $cliente)
        <li>
            <a
                href="{{ route('clientes.show', $cliente) }}"
                class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:border-indigo-200 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-900/50"
            >
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-base font-bold text-white shadow-md shadow-indigo-600/25">
                    {{ $cliente->iniciaisAvatar() }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $cliente->nome }}</span>
                        <span
                            class="h-2 w-2 shrink-0 rounded-full {{ $cliente->email ? 'bg-emerald-500' : 'bg-amber-400' }}"
                            title="{{ $cliente->email ? __('Com e-mail') : __('Sem e-mail') }}"
                        ></span>
                    </div>
                    @if ($cliente->documentoFormatado())
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $cliente->documentoFormatado() }}</p>
                    @endif
                    <div class="mt-2 flex flex-col gap-1 text-xs text-slate-600 dark:text-slate-400 sm:hidden">
                        @if ($cliente->telefoneFormatado())
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.163-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                                {{ $cliente->telefoneFormatado() }}
                            </span>
                        @endif
                        @if ($cliente->email)
                            <span class="inline-flex min-w-0 items-center gap-1">
                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                                <span class="truncate">{{ $cliente->email }}</span>
                            </span>
                        @endif
                    </div>
                </div>
                <div class="hidden min-w-0 shrink-0 flex-col items-end gap-1 text-right text-sm text-slate-600 dark:text-slate-400 sm:flex">
                    @if ($cliente->telefoneFormatado())
                        <span class="inline-flex max-w-[200px] items-center justify-end gap-1.5 lg:max-w-xs">
                            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.163-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                            <span class="truncate tabular-nums">{{ $cliente->telefoneFormatado() }}</span>
                        </span>
                    @endif
                    @if ($cliente->email)
                        <span class="inline-flex max-w-[220px] items-center justify-end gap-1.5 lg:max-w-md">
                            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                            <span class="truncate">{{ $cliente->email }}</span>
                        </span>
                    @endif
                </div>
                <svg class="h-5 w-5 shrink-0 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </li>
    @empty
        <li class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-14 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-400">
            @if ($busca !== '')
                {{ __('Nenhum resultado para esta busca.') }}
            @else
                <p>{{ __('Nenhum cliente cadastrado.') }}</p>
                @can('create', App\Models\Cliente::class)
                    <button
                        type="button"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                        @click="$store.novoCliente.open = true"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ __('Novo cliente') }}
                    </button>
                @endcan
            @endif
        </li>
    @endforelse
</ul>

