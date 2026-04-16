@php
    use App\Support\AulaCurriculoNormam;
@endphp

<x-app-layout :title="__('Atestados de aula')">
    <x-slot name="header">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Escola Náutica') }}</h1>
    </x-slot>

    <x-escola-hub-frame>
        <div class="mb-6 flex flex-col gap-4 border-b border-slate-200/80 pb-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-600 dark:text-slate-400">{{ __('Atestados de aula: consulta por aluno e configuração do plano de treinamento exigido pela NORMAM.') }}</p>
            @include('aulas.partials.hub-turbo-back')
        </div>

        <div class="mx-auto max-w-5xl space-y-6">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @if (in_array($tab, ['ara', 'mta'], true))
                <div class="flex flex-wrap gap-2">
                    <x-escola-nav-pill :href="route('aulas.atestados.index', ['tab' => 'ara'])" :active="$tab === 'ara'">{{ __('ARA — Arrais-Amador') }}</x-escola-nav-pill>
                    <x-escola-nav-pill :href="route('aulas.atestados.index', ['tab' => 'mta'])" :active="$tab === 'mta'">{{ __('MTA — Motonauta') }}</x-escola-nav-pill>
                </div>
            @endif

            @if ($tab === 'lista')
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-900/40">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500 dark:text-slate-400">{{ __('Aluno') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500 dark:text-slate-400">{{ __('CPF') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500 dark:text-slate-400">{{ __('Data da aula') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500 dark:text-slate-400">{{ __('Ofício') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500 dark:text-slate-400">{{ __('Acções') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($linhasPorAulaAluno as $linha)
                                @php
                                    $dataAula = $linha->data_aula ? \Carbon\Carbon::parse($linha->data_aula) : null;
                                    $planoUrl = route('aulas.atestados.index', ['tab' => 'ara']);
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white">{{ $linha->cliente_nome }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $linha->cliente_cpf }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $dataAula?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $linha->numero_oficio }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ $planoUrl }}" data-turbo-frame="nx-escola-hub" data-turbo-action="advance" class="inline-flex rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200">{{ __('Plano de durações') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('Nenhum aluno em aulas registadas.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                @php
                    $programa = $tab === 'mta' ? AulaCurriculoNormam::PROGRAMA_MTA : AulaCurriculoNormam::PROGRAMA_ARA;
                    $blocos = $tab === 'mta' ? $curriculoMta : $curriculoAra;
                @endphp

                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-6 border-b border-slate-200/80 pb-4 dark:border-slate-700" x-data="{ nxDurIntroOpen: true }">
                        <button type="button"
                            class="flex w-full cursor-pointer select-none items-center justify-between gap-3 text-left text-sm font-semibold text-slate-900 dark:text-white"
                            @click="nxDurIntroOpen = ! nxDurIntroOpen"
                            :aria-expanded="nxDurIntroOpen ? 'true' : 'false'"
                        >
                            <span class="min-w-0">{{ __('Durações por conteúdo') }} <span class="font-normal text-slate-500 dark:text-slate-400">({{ strtoupper($tab) }})</span></span>
                            <span class="inline-flex shrink-0 items-center gap-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400">
                                <span x-show="! nxDurIntroOpen" x-cloak>{{ __('Mostrar explicação') }}</span>
                                <span x-show="nxDurIntroOpen" x-cloak>{{ __('Ocultar explicação') }}</span>
                                <svg class="h-4 w-4 shrink-0 transition-transform" :class="nxDurIntroOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </span>
                        </button>
                        <p class="mt-3 text-xs leading-relaxed text-slate-500 dark:text-slate-400" x-show="nxDurIntroOpen" x-transition.opacity.duration.200ms>
                            {{ __('Conteúdos fixos na NORMAM para este programa. Os minutos são guardados por empresa e usados na emissão dos atestados de aula; não estão ligados a uma aula ou a um aluno específicos.') }}
                        </p>
                    </div>

                    @if (auth()->user()?->hasPermission('aulas.manage'))
                        <form method="POST" action="{{ route('aulas.atestados.duracoes.store') }}" class="mt-6 space-y-6">
                            @csrf
                            <input type="hidden" name="programa" value="{{ $programa }}" />

                            @foreach (['teorico' => __('Plano de treinamento teórico'), 'pratico' => __('Plano de treinamento prático')] as $sec => $titulo)
                                <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-4 dark:border-slate-700 dark:bg-slate-900/50">
                                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ strtoupper($tab) }} — {{ $titulo }}</h3>
                                    <div class="mt-4 space-y-3">
                                        @foreach ($blocos[$sec] as $item)
                                            @php
                                                $row = $duracoesMap[$item['key']] ?? null;
                                                $val = old('duracoes.'.$item['key'], $row?->duracao_minutos);
                                            @endphp
                                            <div class="flex flex-col gap-2 border-b border-slate-200/80 pb-3 last:border-0 dark:border-slate-700 sm:flex-row sm:items-center sm:justify-between">
                                                <label class="flex-1 text-sm text-slate-700 dark:text-slate-200" for="d_{{ $item['key'] }}">{{ $item['label'] }}</label>
                                                <div class="flex items-center gap-2 shrink-0">
                                                    <input
                                                        id="d_{{ $item['key'] }}"
                                                        type="number"
                                                        name="duracoes[{{ $item['key'] }}]"
                                                        min="0"
                                                        max="9999"
                                                        value="{{ $val }}"
                                                        class="w-28 rounded-lg border-slate-300 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                                                    />
                                                    <span class="text-xs text-slate-500">{{ __('min') }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            <x-primary-button type="submit">{{ __('Guardar plano de durações') }}</x-primary-button>
                        </form>
                    @else
                        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">{{ __('Apenas utilizadores com permissão de gestão de aulas podem editar o plano.') }}</p>
                        <div class="mt-6 space-y-6 opacity-90">
                            @foreach (['teorico' => __('Plano de treinamento teórico'), 'pratico' => __('Plano de treinamento prático')] as $sec => $titulo)
                                <div class="rounded-xl border border-slate-200/80 bg-slate-50/60 p-4 dark:border-slate-700 dark:bg-slate-900/50">
                                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ strtoupper($tab) }} — {{ $titulo }}</h3>
                                    <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-slate-600 dark:text-slate-300">
                                        @foreach ($blocos[$sec] as $item)
                                            @php $row = $duracoesMap[$item['key']] ?? null; @endphp
                                            <li>
                                                {{ $item['label'] }}
                                                @if ($row && $row->duracao_minutos !== null)
                                                    <span class="text-slate-500">({{ $row->duracao_minutos }} {{ __('min') }})</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </x-escola-hub-frame>
</x-app-layout>
