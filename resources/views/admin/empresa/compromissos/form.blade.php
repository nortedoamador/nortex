@php
    $edicao = $compromisso->exists;
@endphp
<x-app-layout title="{{ $tituloPagina }}">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ $tituloPagina }}</h2>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-xl space-y-4">
            <form method="POST" action="{{ $edicao ? route('admin.empresa.compromissos.update', $compromisso) : route('admin.empresa.compromissos.store') }}" class="space-y-4">
                @csrf
                @if ($edicao)
                    @method('PATCH')
                @endif

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                    @include('admin.empresa.compromissos._form-fields', ['compromisso' => $compromisso, 'tipos' => $tipos, 'idPrefix' => 'ec'])
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Guardar') }}</button>
                    <a href="{{ route('admin.empresa.compromissos.index') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Cancelar') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
