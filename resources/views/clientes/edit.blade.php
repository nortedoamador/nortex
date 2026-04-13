<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Editar cliente') }}</h2>
                <p class="text-sm text-slate-600 dark:text-slate-400">{{ __('Ficha de cadastro') }}</p>
            </div>
            <a href="{{ route('clientes.show', $cliente) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('← Voltar') }}</a>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                <form
                    method="POST"
                    action="{{ route('clientes.update', $cliente) }}"
                    enctype="multipart/form-data"
                    class="space-y-6"
                    data-cliente-ficha
                    data-capitais='@json(\App\Support\BrasilCapitais::porUf())'
                    data-msg-selecione-municipio="{{ __('Selecione o município') }}"
                >
                    @csrf
                    @method('PATCH')
                    @include('clientes.partials.form-ficha-campos', ['cliente' => $cliente, 'ufs' => $ufs])
                    @include('clientes.partials.form-ficha-uploads')
                    <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-6 dark:border-slate-700">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                            {{ __('Salvar') }}
                        </button>
                        <a href="{{ route('clientes.show', $cliente) }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">{{ __('Cancelar') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
