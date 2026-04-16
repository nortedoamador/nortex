<x-app-layout :title="__('Comunicados de aula')">
    <x-slot name="header">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ __('Escola Náutica') }}</h1>
    </x-slot>

    <x-escola-hub-frame>
        <div class="mb-6 flex flex-col gap-4 border-b border-slate-200/80 pb-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Comunicados — envio e PDF') }}</p>
            @include('aulas.partials.hub-turbo-back')
        </div>

        <div class="mx-auto max-w-5xl space-y-6">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ __('O cliente de e-mail não anexa ficheiros automaticamente; o corpo da mensagem inclui a ligação para descarregar o PDF do comunicado.') }}
            </p>

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-900/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500 dark:text-slate-400">{{ __('Data') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500 dark:text-slate-400">{{ __('Local') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500 dark:text-slate-400">{{ __('Ofício') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-bold uppercase text-slate-500 dark:text-slate-400">{{ __('Comunicado') }}</th>
                            @if (auth()->user()?->hasPermission('aulas.manage'))
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500 dark:text-slate-400">{{ __('Registo') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($aulas as $aula)
                            @php
                                $pdfUrl = url(route('aulas.pdf.comunicado', $aula, false));
                                $subject = rawurlencode(__('Comunicado de aula').' '.$aula->numero_oficio);
                                $body = rawurlencode(__('Anexe o PDF do comunicado descarregado em:')."\n\n".$pdfUrl);
                                $mailto = 'mailto:?subject='.$subject.'&body='.$body;
                                $enviado = $aula->comunicado_enviado_em !== null;
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-200">{{ optional($aula->data_aula)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $aula->local }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white">{{ $aula->numero_oficio }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if (auth()->user()?->hasPermission('aulas.manage'))
                                        <a
                                            href="{{ $mailto }}"
                                            class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full px-2 py-1 text-xs font-bold {{ $enviado ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200' : 'bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-200' }}"
                                            title="{{ __('Abrir mensagem de e-mail') }}"
                                        >{{ $enviado ? __('Ok') : '×' }}</a>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                @if (auth()->user()?->hasPermission('aulas.manage'))
                                    <td class="px-4 py-3 text-right text-sm">
                                        <form method="POST" action="{{ route('aulas.comunicado-enviado', $aula) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="enviado" value="{{ $enviado ? '0' : '1' }}" />
                                            <button type="submit" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                                {{ $enviado ? __('Marcar não enviado') : __('Marcar enviado') }}
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()?->hasPermission('aulas.manage') ? 5 : 4 }}" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('Nenhuma aula registada.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-escola-hub-frame>
</x-app-layout>
