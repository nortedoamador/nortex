<x-platform-layout :title="__('Editar tipo de processo')">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Editar tipo de processo') }} — {{ $tipo->nome }}</h2>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->has('checklist'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">
                {{ $errors->first('checklist') }}
            </div>
        @endif

        <div class="max-w-xl space-y-4">
            <form method="POST" action="{{ route('platform.cadastros.tipos-processo.update', $tipo) }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Nome') }}</label>
                        <input name="nome" value="{{ old('nome', $tipo->nome) }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('nome')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Slug') }}</label>
                        <input name="slug" value="{{ old('slug', $tipo->slug) }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                        @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Categoria') }}</label>
                        <select name="categoria" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="">{{ __('(sem categoria)') }}</option>
                            @foreach ($categorias as $c)
                                <option value="{{ $c->value }}" @selected(old('categoria', $tipo->categoria) === $c->value)>{{ $c->label() }}</option>
                            @endforeach
                        </select>
                        @error('categoria')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Ordem') }}</label>
                            <input type="number" min="0" max="32767" name="ordem" value="{{ old('ordem', $tipo->ordem) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                            @error('ordem')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                <input type="hidden" name="ativo" value="0" />
                                <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $tipo->ativo)) class="rounded border-slate-300 text-violet-600" />
                                {{ __('Ativo') }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Guardar') }}</button>
                    <a href="{{ route('platform.cadastros.tipos-processo.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold dark:border-slate-600">{{ __('Voltar') }}</a>
                </div>
            </form>
        </div>

        @if ($tenantTipo && $checklistEmpresa)
            <div class="space-y-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ __('Checklist de documentos') }}</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ __('Tipos de documento da empresa de referência «:e» (ID :id). Arraste pelo ícone para ordenar.', ['e' => $checklistEmpresa->nome, 'id' => $checklistEmpresa->id]) }}
                    </p>
                </div>

                @php
                    $includedDocs = $tenantTipo->documentoRegras;
                    $includedIds = $includedDocs->pluck('id')->all();
                    $excludedDocs = $documentoTipos->filter(fn ($dt) => ! in_array($dt->id, $includedIds, true))->values();
                @endphp
                <form method="POST" action="{{ route('platform.cadastros.tipos-processo.update-regras', $tipo) }}" data-nx-checklist-sort class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="border-b border-slate-200 px-4 py-2 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">
                            {{ __('Edite código, nome e modelo (slug do PDF) por linha. Marque «Incluir» para adicionar ao checklist — a linha passa para a secção de incluídos sem recarregar a página.') }}
                        </p>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                                <thead class="bg-slate-50 dark:bg-slate-800/50">
                                    <tr>
                                        <th scope="col" class="w-12 px-2 py-2 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400" aria-hidden="true"></th>
                                        <th scope="col" class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Incluir') }}</th>
                                        <th scope="col" class="w-28 min-w-[5.5rem] px-3 py-2 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Código') }}</th>
                                        <th scope="col" class="min-w-[min(100%,280px)] px-3 py-2 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Nome') }}</th>
                                        <th scope="col" class="min-w-[140px] px-3 py-2 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Modelo (slug)') }}</th>
                                        <th scope="col" class="w-28 px-3 py-2 text-left text-xs font-semibold uppercase text-slate-600 dark:text-slate-400">{{ __('Obrigatório') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800" data-nx-checklist-tbody-included>
                                    <tr class="bg-slate-50/80 dark:bg-slate-800/40">
                                        <td colspan="6" class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                            {{ __('Incluídos no checklist') }}
                                        </td>
                                    </tr>
                                    @foreach ($includedDocs as $dt)
                                        @include('platform.cadastros.tipos-processo.partials.checklist-doc-row', ['dt' => $dt, 'tenantTipo' => $tenantTipo, 'included' => true])
                                    @endforeach
                                </tbody>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800" data-nx-checklist-tbody-excluded>
                                    <tr class="bg-slate-50/80 dark:bg-slate-800/40">
                                        <td colspan="6" class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">
                                            {{ __('Não incluídos') }}
                                        </td>
                                    </tr>
                                    @foreach ($excludedDocs as $dt)
                                        @include('platform.cadastros.tipos-processo.partials.checklist-doc-row', ['dt' => $dt, 'tenantTipo' => $tenantTipo, 'included' => false])
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Guardar checklist') }}</button>
                    </div>
                </form>
            </div>
        @else
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200">
                {{ __('Checklist indisponível: não há empresa na base ou defina NORTEX_PLATFORM_CHECKLIST_EMPRESA_ID no .env.') }}
            </div>
        @endif
    </div>
</x-platform-layout>
