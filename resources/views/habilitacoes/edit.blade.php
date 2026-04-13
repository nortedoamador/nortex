<x-app-layout title="{{ __('Editar habilitação') }}">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Editar habilitação') }}</h2>
            <a href="{{ route('habilitacoes.show', $habilitacao) }}" class="text-sm font-medium text-slate-600 hover:text-slate-500 dark:text-slate-400">{{ __('← Ficha') }}</a>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <form
                    method="POST"
                    action="{{ route('habilitacoes.update', $habilitacao) }}"
                    class="grid gap-3 md:grid-cols-3"
                >
                    @csrf
                    @method('PATCH')
                    @include('habilitacoes.partials.form-campos', [
                        'clientes' => $clientes,
                        'clientesSuggest' => $clientesSuggest,
                        'idPrefix' => 'edit_',
                        'habilitacao' => $habilitacao,
                    ])

                    <div class="md:col-span-2 flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('habilitacoes.show', $habilitacao) }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                            {{ __('Cancelar') }}
                        </a>
                        <x-primary-button type="submit" class="!rounded-xl !px-5 !py-2.5">
                            {{ __('Salvar') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
