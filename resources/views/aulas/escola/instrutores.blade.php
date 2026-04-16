@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\EscolaInstrutor> $instrutoresLista */
    /** @var string $sub */
@endphp

<x-app-layout :title="__('Escola Náutica — Instrutores')">
    <x-slot name="header">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Escola Náutica') }}</h1>
    </x-slot>

    <x-escola-hub-frame>
        <div class="mb-6 flex flex-col gap-4 border-b border-slate-200/80 pb-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Instrutores — equipa e carteira CHA') }}</p>
            @include('aulas.partials.hub-turbo-back')
        </div>

        <div
            class="mx-auto max-w-5xl space-y-6"
            x-data
            x-init="window.addEventListener('nx-escola-instrutor-associado', () => { window.location.reload(); })"
        >
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="flex flex-wrap gap-2">
                <x-escola-nav-pill :href="route('aulas.escola.instrutores', ['sub' => 'resumo'])" :active="$sub === 'resumo'">{{ __('Visão geral') }}</x-escola-nav-pill>
                <x-escola-nav-pill :href="route('aulas.escola.instrutores', ['sub' => 'carteira'])" :active="$sub === 'carteira'">{{ __('Carteira') }}</x-escola-nav-pill>
            </div>

            @if ($sub === 'resumo')
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    {{ __('Para associar um novo instrutor, use o') }}
                    <a href="{{ route('aulas.escola.edit') }}" data-turbo-frame="nx-escola-hub" data-turbo-action="advance" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('perfil da escola') }}</a>.
                </p>

                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-900/40">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500 dark:text-slate-400">{{ __('Nome') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500 dark:text-slate-400">{{ __('CPF') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500 dark:text-slate-400">{{ __('CHA nº') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500 dark:text-slate-400">{{ __('Validade CHA') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($instrutoresLista as $ins)
                                @php $c = $ins->cliente; @endphp
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white">{{ $c?->nome ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $c?->cpf ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $ins->cha_numero ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $ins->cha_data_validade?->format('d/m/Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('Ainda não há instrutores associados.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="space-y-6">
                    @foreach ($instrutoresLista as $ins)
                        @php $c = $ins->cliente; @endphp
                        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $c?->nome ?? '—' }} <span class="font-normal text-slate-500">{{ $c?->cpf }}</span></p>
                            <form method="POST" action="{{ route('aulas.escola.instrutores.update', $ins) }}" class="mt-3 grid gap-3 md:grid-cols-2">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <x-input-label :for="'cha_n_'.$ins->id" :value="__('Nº CHA')" />
                                    <x-text-input :id="'cha_n_'.$ins->id" name="cha_numero" class="mt-1 block w-full" :value="old('cha_numero', $ins->cha_numero)" />
                                </div>
                                <div>
                                    <x-input-label :for="'cha_cat_'.$ins->id" :value="__('Categoria CHA')" />
                                    <x-text-input :id="'cha_cat_'.$ins->id" name="cha_categoria" class="mt-1 block w-full" :value="old('cha_categoria', $ins->cha_categoria)" />
                                </div>
                                <div>
                                    <x-input-label :for="'cha_de_'.$ins->id" :value="__('Data emissão CHA')" />
                                    <input :id="'cha_de_'.$ins->id" type="date" name="cha_data_emissao" value="{{ old('cha_data_emissao', $ins->cha_data_emissao?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100" />
                                </div>
                                <div>
                                    <x-input-label :for="'cha_dv_'.$ins->id" :value="__('Validade CHA')" />
                                    <input :id="'cha_dv_'.$ins->id" type="date" name="cha_data_validade" value="{{ old('cha_data_validade', $ins->cha_data_validade?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label :for="'cha_j_'.$ins->id" :value="__('Jurisdição CHA')" />
                                    <x-text-input :id="'cha_j_'.$ins->id" name="cha_jurisdicao" class="mt-1 block w-full" :value="old('cha_jurisdicao', $ins->cha_jurisdicao)" />
                                </div>
                                <div class="md:col-span-2 flex flex-wrap gap-2">
                                    <x-secondary-button type="submit">{{ __('Guardar CHA') }}</x-secondary-button>
                                </div>
                            </form>
                            <form method="POST" action="{{ route('aulas.escola.instrutores.destroy', $ins) }}" class="mt-2" onsubmit="return confirm(@js(__('Remover este instrutor da escola?')))">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400">{{ __('Remover instrutor') }}</button>
                            </form>
                        </div>
                    @endforeach

                    @if ($instrutoresLista->isEmpty())
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Não há instrutores. Associe um no perfil da escola.') }}</p>
                    @endif
                </div>
            @endif
        </div>

        @include('aulas.partials.modal-novo-cliente-instrutor-escola')
    </x-escola-hub-frame>
</x-app-layout>
