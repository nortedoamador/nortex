<x-tenant-admin-layout title="{{ __('Checklist') }}">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Checklist de documentos') }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $tipoProcesso->nome }} <code class="text-xs">{{ $tipoProcesso->slug }}</code></p>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl space-y-4">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</div>
            @endif

            @php
                $includedDocs = $tipoProcesso->documentoRegras;
                $includedIds = $includedDocs->pluck('id')->all();
                $excludedDocs = $documentoTipos->filter(fn ($dt) => ! in_array($dt->id, $includedIds, true))->values();
            @endphp
            <form method="POST" action="{{ tenant_admin_route('tipo-processos.update-regras', $tipoProcesso) }}" data-nx-checklist-sort>
                @csrf
                @method('PUT')
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-4 py-3 text-sm font-semibold dark:border-slate-800">{{ __('Tipos de documento') }}</div>
                    <p class="border-b border-slate-200 px-4 py-2 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">
                        {{ __('Incluídos e não incluídos em secções separadas. Ao marcar «Incluir», a linha passa para incluídos sem recarregar. Arraste pelo ícone à esquerda para alterar a ordem entre os incluídos.') }}
                    </p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-800/50">
                                <tr>
                                    <th scope="col" class="w-12 px-2 py-2 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400" aria-hidden="true"></th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Incluir') }}</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Documento') }}</th>
                                    <th scope="col" class="w-24 px-4 py-2 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Obrigatório') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800" data-nx-checklist-tbody-included>
                                <tr class="bg-slate-50/80 dark:bg-slate-800/40">
                                    <td colspan="4" class="px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                        {{ __('Incluídos no checklist') }}
                                    </td>
                                </tr>
                                @foreach ($includedDocs as $dt)
                                    @php
                                        $pivot = $tipoProcesso->documentoRegras->firstWhere('id', $dt->id);
                                        $included = true;
                                    @endphp
                                    <tr class="nx-checklist-tr bg-white dark:bg-slate-900" data-doc-tipo-id="{{ $dt->id }}">
                                        <td class="w-12 px-2 py-2 align-middle text-slate-400">
                                            <span
                                                data-nx-drag-handle
                                                draggable="true"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-transparent text-slate-400 cursor-grab active:cursor-grabbing"
                                                aria-disabled="false"
                                                title="{{ __('Arrastar para ordenar') }}"
                                                role="button"
                                                tabindex="-1"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.008v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.008v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.008v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                                </svg>
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 align-middle">
                                            <input type="checkbox" name="linhas[{{ $dt->id }}][ativo]" value="1" @checked($included) class="rounded border-slate-300 text-indigo-600" />
                                            <input type="hidden" name="linhas[{{ $dt->id }}][documento_tipo_id]" value="{{ $dt->id }}" />
                                            <input
                                                type="hidden"
                                                class="nx-linha-ordem"
                                                name="linhas[{{ $dt->id }}][ordem]"
                                                value="{{ old('linhas.'.$dt->id.'.ordem', $pivot?->pivot->ordem ?? 0) }}"
                                            />
                                        </td>
                                        <td class="px-4 py-2 text-sm text-slate-900 dark:text-slate-100">
                                            {{ $dt->nome }}
                                            <div class="text-xs text-slate-500"><code>{{ $dt->codigo }}</code></div>
                                        </td>
                                        <td class="px-4 py-2 align-middle">
                                            <input type="checkbox" name="linhas[{{ $dt->id }}][obrigatorio]" value="1" @checked(old('linhas.'.$dt->id.'.obrigatorio', $pivot?->pivot->obrigatorio ?? true)) class="rounded border-slate-300 text-indigo-600" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800" data-nx-checklist-tbody-excluded>
                                <tr class="bg-slate-50/80 dark:bg-slate-800/40">
                                    <td colspan="4" class="px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                        {{ __('Não incluídos') }}
                                    </td>
                                </tr>
                                @foreach ($excludedDocs as $dt)
                                    @php
                                        $pivot = $tipoProcesso->documentoRegras->firstWhere('id', $dt->id);
                                        $included = false;
                                    @endphp
                                    <tr class="nx-checklist-tr bg-white dark:bg-slate-900" data-doc-tipo-id="{{ $dt->id }}">
                                        <td class="w-12 px-2 py-2 align-middle text-slate-400">
                                            <span
                                                data-nx-drag-handle
                                                draggable="false"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-transparent text-slate-400 cursor-not-allowed opacity-40 pointer-events-none"
                                                aria-disabled="true"
                                                title="{{ __('Arrastar para ordenar') }}"
                                                role="button"
                                                tabindex="-1"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.008v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.008v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.008v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                                </svg>
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 align-middle">
                                            <input type="checkbox" name="linhas[{{ $dt->id }}][ativo]" value="1" @checked($included) class="rounded border-slate-300 text-indigo-600" />
                                            <input type="hidden" name="linhas[{{ $dt->id }}][documento_tipo_id]" value="{{ $dt->id }}" />
                                            <input
                                                type="hidden"
                                                class="nx-linha-ordem"
                                                name="linhas[{{ $dt->id }}][ordem]"
                                                value="{{ old('linhas.'.$dt->id.'.ordem', $pivot?->pivot->ordem ?? 0) }}"
                                            />
                                        </td>
                                        <td class="px-4 py-2 text-sm text-slate-900 dark:text-slate-100">
                                            {{ $dt->nome }}
                                            <div class="text-xs text-slate-500"><code>{{ $dt->codigo }}</code></div>
                                        </td>
                                        <td class="px-4 py-2 align-middle">
                                            <input type="checkbox" name="linhas[{{ $dt->id }}][obrigatorio]" value="1" @checked(old('linhas.'.$dt->id.'.obrigatorio', $pivot?->pivot->obrigatorio ?? true)) class="rounded border-slate-300 text-indigo-600" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Guardar checklist') }}</button>
                    <a href="{{ tenant_admin_route('tipo-processos.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold dark:border-slate-600">{{ __('Voltar') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-tenant-admin-layout>
