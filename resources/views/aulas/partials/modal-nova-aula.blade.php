@php
    /** @var \Illuminate\Support\Collection|array $instrutores */
    /** @var \Illuminate\Support\Collection $tiposAula */
@endphp

<div
    x-show="$store.novaAula.open"
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
    aria-labelledby="modal-nova-aula-titulo"
    @keydown.escape.window="$store.novaAula.open = false"
>
    <div
        class="absolute inset-0"
        aria-hidden="true"
        @click="$store.novaAula.open = false"
    ></div>

    <div
        class="relative flex max-h-[min(90vh,900px)] w-[min(100vw-1.5rem,720px)] flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900"
        @click.stop
    >
        <div class="flex shrink-0 items-center justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-slate-700 sm:px-6">
            <h2 id="modal-nova-aula-titulo" class="text-lg font-semibold text-slate-900 dark:text-white">
                {{ __('Nova Aula') }}
            </h2>
            <button
                type="button"
                class="rounded-lg p-1.5 text-red-600 transition hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-950/50 dark:hover:text-red-300"
                @click="$store.novaAula.open = false"
                aria-label="{{ __('Fechar') }}"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4 sm:px-6 sm:py-5">
            <form
                method="POST"
                action="{{ route('aulas.store') }}"
                class="space-y-4"
                x-data="nxAulaNauticaForm({
                    initialAlunos: [],
                    initialInstrutores: [],
                    csrf: @js(csrf_token()),
                    buscarCpfUrl: @js(route('alunos.buscar-cpf')),
                    buscarEscolaInstrutorCpfUrl: @js(route('alunos.buscar-escola-instrutor-cpf')),
                    modalStoreUrl: @js(route('alunos.modal-store')),
                })"
            >
                @csrf

                @include('aulas.partials.form-aula-fields', [
                    'idPrefix' => 'modal_',
                    'aula' => null,
                    'isEdit' => false,
                    'tiposAula' => $tiposAula ?? [],
                    'escolaInstrutores' => $escolaInstrutores ?? [],
                ])

                <div class="sticky bottom-0 border-t border-slate-200 bg-white pt-4 dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
                        <div class="flex flex-wrap gap-2">
                            <x-primary-button type="submit">{{ __('Criar aula') }}</x-primary-button>
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200"
                                @click="$store.novaAula.open = false"
                            >
                                {{ __('Cancelar') }}
                            </button>
                        </div>
                        <a
                            href="{{ route('aulas.create') }}"
                            data-turbo-frame="nx-escola-hub"
                            data-turbo-action="advance"
                            class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                            @click="$store.novaAula.open = false"
                        >
                            {{ __('Abrir em página completa') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@include('aulas.partials.form-aula-novo-aluno-modal')
@include('aulas.partials.modal-novo-cliente-instrutor-escola')
@include('aulas.partials.form-aula-scripts')
