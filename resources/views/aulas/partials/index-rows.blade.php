@forelse ($aulas as $aula)
    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
        <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white">{{ $aula->numero_oficio }}</td>
        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ optional($aula->data_aula)->format('d/m/Y') }}</td>
        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $aula->local }}</td>
        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
            @php
                $tipoLabel = match ((string)($aula->tipo_aula ?? '')) {
                    'teorica' => __('Teórica'),
                    'pratica' => __('Prática'),
                    'teorica_pratica' => __('Teórica e Prática'),
                    default => (string)($aula->tipo_aula ?? '—'),
                };
            @endphp
            {{ $tipoLabel }}
        </td>
        <td class="px-4 py-3 text-center text-sm text-slate-700 dark:text-slate-200">{{ $aula->alunos_count ?? 0 }}</td>
        <td class="px-4 py-3 text-center text-sm text-slate-700 dark:text-slate-200">{{ ($aula->escola_instrutores_count ?? 0) + ($aula->instrutores_count ?? 0) }}</td>
        <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $aula->status }}</td>
        <td class="px-4 py-3 text-right text-sm">
            <div class="inline-flex flex-wrap justify-end gap-2">
                <a href="{{ route('aulas.show', $aula) }}" data-turbo-frame="nx-escola-hub" data-turbo-action="advance" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Visualizar') }}</a>
                @if (auth()->user()?->hasPermission('aulas.manage'))
                    <a href="{{ route('aulas.edit', $aula) }}" data-turbo-frame="nx-escola-hub" data-turbo-action="advance" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Editar') }}</a>
                @endif
                @if (auth()->user()?->hasPermission('aulas.view'))
                    <div class="flex items-center gap-2">
                        <a href="{{ route('aulas.pdf.comunicado', $aula) }}" data-turbo="false" target="_blank" rel="noopener noreferrer" class="text-slate-700 hover:text-slate-900 dark:text-slate-200 dark:hover:text-white">{{ __('Comunicado') }}</a>
                        <a href="{{ route('aulas.show', $aula) }}" data-turbo-frame="nx-escola-hub" data-turbo-action="advance" class="text-slate-700 hover:text-slate-900 dark:text-slate-200 dark:hover:text-white">{{ __('ARA') }}</a>
                        <a href="{{ route('aulas.pdf.mta', $aula) }}" data-turbo="false" target="_blank" rel="noopener noreferrer" class="text-slate-700 hover:text-slate-900 dark:text-slate-200 dark:hover:text-white">{{ __('MTA') }}</a>
                    </div>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
            {{ __('Nenhuma aula encontrada.') }}
        </td>
    </tr>
@endforelse

