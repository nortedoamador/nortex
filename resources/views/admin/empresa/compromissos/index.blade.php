<x-app-layout title="{{ __('Compromissos da agenda') }}">
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Compromissos da agenda') }}</h2>
            <a href="{{ route('admin.empresa.compromissos.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Novo compromisso') }}</a>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl space-y-4">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</div>
            @endif

            <p class="text-sm text-slate-600 dark:text-slate-400">{{ __('Reuniões e dias de atendimento na Marinha aparecem no cartão «Agenda» do dashboard. As aulas náuticas são listadas automaticamente a partir do módulo Escola Náutica.') }}</p>

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Data') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Tipo') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Título') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse ($compromissos as $c)
                            <tr class="align-top hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-800 dark:text-slate-200">
                                    {{ $c->data?->format('d/m/Y') }}
                                    @if (filled($c->hora_inicio))
                                        <span class="block text-xs text-slate-500">{{ \Illuminate\Support\Str::substr((string) $c->hora_inicio, 0, 5) }}@if (filled($c->hora_fim))–{{ \Illuminate\Support\Str::substr((string) $c->hora_fim, 0, 5) }}@endif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">{{ $c->tipo_label }}</td>
                                <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-200">{{ $c->titulo }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                    <a href="{{ route('admin.empresa.compromissos.edit', $c) }}" class="font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Editar') }}</a>
                                    <form action="{{ route('admin.empresa.compromissos.destroy', $c) }}" method="post" class="ml-3 inline" onsubmit="return confirm(@js(__('Remover este compromisso?')))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-semibold text-red-600 hover:text-red-500 dark:text-red-400">{{ __('Remover') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('Nenhum compromisso registado.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('admin.empresa.edit') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200">{{ __('← Dados da empresa') }}</a>
                {{ $compromissos->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
