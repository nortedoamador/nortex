@php
    /** @var array<string, string> $ufs */
    $ufs = $ufs ?? \App\Support\BrasilEstados::options();
@endphp

<x-modal name="novo-cliente-instrutor-escola" maxWidth="5xl" focusable>
    <div class="flex max-h-[min(92vh,920px)] flex-col overflow-hidden bg-white dark:bg-slate-900">
        <div class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-slate-700 sm:px-6">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Cadastrar instrutor') }}</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Os dados seguem a ficha de cadastro de clientes. Após guardar, o instrutor é associado à escola com os dados da CHA (opcional), por baixo dos anexos de CNH e comprovante.') }}</p>
            </div>
            <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800" @click="$dispatch('close-modal', 'novo-cliente-instrutor-escola')">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4 sm:px-6 sm:py-5">
            <form
                id="form-novo-cliente-instrutor-escola"
                method="POST"
                action="{{ route('clientes.modal-store') }}"
                enctype="multipart/form-data"
                class="space-y-5"
                data-cliente-ficha
                data-capitais='@json(\App\Support\BrasilCapitais::porUf())'
                data-msg-selecione-municipio="{{ __('Selecione o município') }}"
                x-data="nxNovoClienteInstrutorEscolaForm()"
                @submit.prevent="submitCliente()"
            >
                @csrf
                @include('clientes.partials.form-ficha-campos', ['cliente' => null, 'ufs' => $ufs, 'layout' => 'stacked_sections'])
                @include('clientes.partials.form-ficha-uploads', ['nxInstrutorChaAfterPrincipalAnexos' => true])

                <div class="sticky bottom-0 border-t border-slate-200 bg-white pt-4 dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <button type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" @click="$dispatch('close-modal', 'novo-cliente-instrutor-escola')">{{ __('Cancelar') }}</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60" :disabled="loading">
                            <span x-show="!loading">{{ __('Guardar') }}</span>
                            <span x-show="loading" x-cloak>{{ __('A guardar…') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-modal>
