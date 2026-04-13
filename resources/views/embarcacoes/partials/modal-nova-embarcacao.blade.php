{{-- Modal: Alpine.store('novaEmbarcacao'); $clientes para datalist/CPF --}}
@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Cliente> $clientes */
@endphp

<div
    x-show="$store.novaEmbarcacao.open"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/70 px-0 py-6 sm:py-10"
    style="display: none;"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-nova-embarcacao-titulo"
>
    <div
        class="flex max-h-[min(90vh,900px)] w-[85vw] flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900"
        @click.stop
    >
        <div class="flex shrink-0 items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-slate-700 sm:px-6">
            <h2 id="modal-nova-embarcacao-titulo" class="text-lg font-semibold text-slate-900 dark:text-white">
                {{ __('Nova embarcação') }}
            </h2>
            <button
                type="button"
                class="rounded-lg p-1.5 text-red-600 transition hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-950/50 dark:hover:text-red-300"
                @click="$store.novaEmbarcacao.open = false"
                aria-label="{{ __('Fechar') }}"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4 sm:px-6 sm:py-5">
            <form method="POST" action="{{ route('embarcacoes.store') }}" enctype="multipart/form-data" class="grid gap-4">
                @csrf
                @include('embarcacoes.partials.form-cadastro-campos', [
                    'clientes' => $clientes,
                    'idPrefix' => 'modal_',
                    'incluirFotosCadastro' => false,
                ])

                <div class="sticky bottom-0 border-t border-slate-200 bg-white pt-4 dark:border-slate-700 dark:bg-slate-900">
                    <button
                        type="submit"
                        class="w-full rounded-xl bg-blue-600 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                    >
                        {{ __('Salvar embarcação') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
