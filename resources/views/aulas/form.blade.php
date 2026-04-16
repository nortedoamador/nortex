@php
    /** @var \App\Models\AulaNautica|null $aula */
    use App\Support\AulaEscolaInstrutorProgramaAtestado;

    $isEdit = $aula !== null;
@endphp

<x-app-layout :title="$isEdit ? __('Editar aula') : __('Nova aula')">
    <x-slot name="header">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Escola Náutica') }}</h1>
    </x-slot>

    <x-escola-hub-frame>
        <div class="mb-6 flex flex-col gap-4 border-b border-slate-200/80 pb-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $isEdit ? __('Editar aula') : __('Nova aula') }}</p>
            @include('aulas.partials.hub-turbo-back')
        </div>

        <div class="mx-auto max-w-5xl">
            <form
                method="POST"
                action="{{ $isEdit ? route('aulas.update', $aula) : route('aulas.store') }}"
                class="space-y-6"
                x-data="nxAulaNauticaForm({
                    initialAlunos: @js($isEdit ? $aula->alunos->map(fn($c)=>['id'=>$c->id,'nome'=>$c->nome,'cpf'=>$c->cpf])->values()->all() : []),
                    initialInstrutores: @js($isEdit ? $aula->escolaInstrutores->map(fn ($e) => [
                        'id' => $e->id,
                        'nome' => $e->cliente?->nome ?? '',
                        'cpf' => $e->cliente?->cpf ?? '',
                        'cha' => $e->cha_numero ?? '',
                        'programa_atestado' => $e->pivot->programa_atestado ?? AulaEscolaInstrutorProgramaAtestado::AMBOS,
                    ])->values()->all() : []),
                    csrf: @js(csrf_token()),
                    buscarCpfUrl: @js(route('alunos.buscar-cpf')),
                    buscarEscolaInstrutorCpfUrl: @js(route('alunos.buscar-escola-instrutor-cpf')),
                    modalStoreUrl: @js(route('alunos.modal-store')),
                })"
            >
                @csrf
                @if ($isEdit)
                    @method('PATCH')
                @endif

                @include('aulas.partials.form-aula-fields', [
                    'idPrefix' => '',
                    'aula' => $aula,
                    'isEdit' => $isEdit,
                    'tiposAula' => $tiposAula ?? [],
                    'escolaInstrutores' => $escolaInstrutores,
                ])

                <div class="flex flex-wrap gap-3 border-t border-slate-200 pt-6 dark:border-slate-700">
                    <x-primary-button type="submit">{{ $isEdit ? __('Salvar') : __('Criar aula') }}</x-primary-button>
                    <a href="{{ route('aulas.index') }}" data-turbo-frame="nx-escola-hub" data-turbo-action="advance" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">{{ __('Cancelar') }}</a>
                </div>
            </form>
        </div>

        @include('aulas.partials.form-aula-novo-aluno-modal')
        @include('aulas.partials.modal-novo-cliente-instrutor-escola')
    </x-escola-hub-frame>
</x-app-layout>
