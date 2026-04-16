<x-app-layout :title="__('Aula')">
    <x-slot name="header">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Escola Náutica') }}</h1>
    </x-slot>

    <x-escola-hub-frame>
        <div class="mb-6 flex flex-col gap-4 border-b border-slate-200/80 pb-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Aula') }} — {{ __('Nº Ofício') }} {{ $aula->numero_oficio }}</p>
            <div class="flex flex-wrap gap-2">
                @include('aulas.partials.hub-turbo-back')
                @if (auth()->user()?->hasPermission('aulas.manage'))
                    <a href="{{ route('aulas.edit', $aula) }}" data-turbo-frame="nx-escola-hub" data-turbo-action="advance" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                        {{ __('Editar') }}
                    </a>
                @endif
            </div>
        </div>

        <div class="mx-auto max-w-5xl space-y-4">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Data') }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ optional($aula->data_aula)->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Local') }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">{{ $aula->local }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Tipo da aula') }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                            @php
                                $tipoLabel = match ((string)($aula->tipo_aula ?? '')) {
                                    'teorica' => __('Teórica'),
                                    'pratica' => __('Prática'),
                                    'teorica_pratica' => __('Teórica e Prática'),
                                    default => (string)($aula->tipo_aula ?? '—'),
                                };
                            @endphp
                            {{ $tipoLabel }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Horário') }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $aula->hora_inicio ? substr($aula->hora_inicio, 0, 5) : '—' }} – {{ $aula->hora_fim ? substr($aula->hora_fim, 0, 5) : '—' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Documentos PDF') }}</h3>
                    @if (auth()->user()?->hasPermission('aulas.view'))
                        <div class="flex flex-wrap gap-2">
                            <a class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white" href="{{ route('aulas.pdf.comunicado', $aula) }}" data-turbo="false" target="_blank" rel="noopener noreferrer">{{ __('Comunicado') }}</a>
                            <a class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white" href="{{ route('aulas.pdf.mta', $aula) }}" data-turbo="false" target="_blank" rel="noopener noreferrer">{{ __('MTA (lote)') }}</a>
                        </div>
                    @endif
                </div>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ __('O atestado ARA (Anexo 5-E) é gerado por aluno na lista abaixo.') }}</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Alunos vinculados') }}</h3>
                    <ul class="mt-3 space-y-2">
                        @forelse ($aula->alunos as $aluno)
                            <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-900/40">
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $aluno->nome }}</span>
                                <span class="flex flex-wrap items-center gap-2">
                                    <span class="text-slate-600 dark:text-slate-300">{{ $aluno->cpf }}</span>
                                    @if (auth()->user()?->hasPermission('aulas.view'))
                                        <a class="inline-flex items-center rounded-md bg-slate-900 px-2 py-1 text-xs font-semibold text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white" href="{{ route('aulas.pdf.ara', [$aula, $aluno]) }}" data-turbo="false" target="_blank" rel="noopener noreferrer">{{ __('ARA') }}</a>
                                    @endif
                                </span>
                            </li>
                        @empty
                            <li class="text-sm text-slate-500 dark:text-slate-400">{{ __('Nenhum aluno vinculado.') }}</li>
                        @endforelse
                    </ul>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Instrutores (ETN)') }}</h3>
                    <ul class="mt-3 space-y-2">
                        @forelse ($aula->escolaInstrutores as $ei)
                            @php $c = $ei->cliente; @endphp
                            <li class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-900/40">
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $c?->nome ?? '—' }}</span>
                                <span class="block text-xs text-slate-600 dark:text-slate-300">{{ $c?->cpf }}@if ($ei->cha_numero) — CHA {{ $ei->cha_numero }}@endif</span>
                            </li>
                        @empty
                            <li class="text-sm text-slate-500 dark:text-slate-400">{{ __('Nenhum instrutor ETN vinculado.') }}</li>
                        @endforelse
                    </ul>
                    @if ($aula->instrutores->isNotEmpty())
                        <p class="mt-3 text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Instrutores (equipe — legado)') }}</p>
                        <ul class="mt-2 space-y-2">
                            @foreach ($aula->instrutores as $instrutor)
                                <li class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm dark:border-slate-800 dark:bg-slate-900/40">
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ $instrutor->name }}</span>
                                    <span class="text-slate-600 dark:text-slate-300">{{ $instrutor->email }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </x-escola-hub-frame>
</x-app-layout>
