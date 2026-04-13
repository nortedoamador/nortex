<x-app-layout title="{{ __('Editar embarcação') }}">
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Editar embarcação') }}</h2>
            <a href="{{ route('embarcacoes.show', $embarcacao) }}" class="text-sm font-medium text-slate-600 hover:text-slate-500 dark:text-slate-400">{{ __('← Ficha') }}</a>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-[1000px]">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200" role="alert">
                        <p class="font-semibold">{{ __('Não foi possível guardar. Corrija os campos abaixo.') }}</p>
                        <ul class="mt-2 list-inside list-disc space-y-0.5">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('embarcacoes.update', $embarcacao) }}" enctype="multipart/form-data" class="grid gap-4">
                    @csrf
                    @method('PATCH')
                    @include('embarcacoes.partials.form-cadastro-campos', ['clientes' => $clientes, 'idPrefix' => '', 'embarcacao' => $embarcacao])

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('embarcacoes.show', $embarcacao) }}" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                            {{ __('Cancelar') }}
                        </a>
                        <x-primary-button type="submit" class="!rounded-xl !px-5 !py-2.5">
                            {{ __('Salvar alterações') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
