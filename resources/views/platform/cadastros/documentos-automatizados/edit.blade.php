<x-platform-layout :title="__('Editar documento automático global')">
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ __('Editar documento automático global') }} — {{ $modelo->titulo }}</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Alterações aqui não sobrescrevem empresas personalizadas até usar «Propagar».') }}</p>
    </x-slot>

    <div class="max-w-4xl space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <p class="text-sm text-slate-600 dark:text-slate-400">{{ __('Modelos de empresa ligados a este global: :n', ['n' => $refsEmpresa]) }}</p>

        <form method="POST" action="{{ route('platform.cadastros.documentos-automatizados.update', $modelo) }}" class="space-y-4">
            @csrf
            @method('PATCH')
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Slug') }}</label>
                    <input name="slug" value="{{ old('slug', $modelo->slug) }}" required pattern="[a-z0-9]+(?:-[a-z0-9]+)*" @readonly($refsEmpresa > 0) class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 font-mono text-sm read-only:bg-slate-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:read-only:bg-slate-800" />
                    @if ($refsEmpresa > 0)
                        <p class="mt-1 text-xs text-slate-500">{{ __('O slug não pode ser alterado enquanto existirem modelos de empresa ligados.') }}</p>
                    @endif
                    @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Título') }}</label>
                    <input name="titulo" value="{{ old('titulo', $modelo->titulo) }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('titulo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Referência') }}</label>
                    <input name="referencia" value="{{ old('referencia', $modelo->referencia) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
                    @error('referencia')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Conteúdo Blade') }}</label>
                    <textarea name="conteudo" rows="18" required class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2 font-mono text-xs leading-relaxed dark:border-slate-700 dark:bg-slate-950 dark:text-white">{{ old('conteudo', $modelo->conteudo) }}</textarea>
                    @error('conteudo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500">{{ __('Guardar') }}</button>
                <a href="{{ route('platform.cadastros.documentos-automatizados.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold dark:border-slate-600">{{ __('Voltar') }}</a>
            </div>
        </form>

        @php
            $escopoOld = old('propagacao_escopo', 'todas');
            $empresaIdsOld = old('empresa_ids', []);
            $empresaIdsOld = is_array($empresaIdsOld) ? array_map('intval', $empresaIdsOld) : [];
            $propagacaoSelectSize = min(12, max(6, $empresas->count()));
        @endphp
        <div class="rounded-2xl border border-amber-200/80 bg-amber-50/80 p-6 dark:border-amber-900/40 dark:bg-amber-950/30" x-data="{ escopo: @js($escopoOld) }">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Propagar para empresas não personalizadas') }}</h3>
            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ __('Atualiza apenas registos com o mesmo vínculo global e personalizado = não. Personalizados e slugs ocultos na empresa são ignorados.') }}</p>
            <form method="POST" action="{{ route('platform.cadastros.documentos-automatizados.propagar', $modelo) }}" class="mt-4 space-y-4" onsubmit="return confirm(@js(__('Propagar este esqueleto às empresas elegíveis conforme o alcance escolhido?')))">
                @csrf
                <fieldset class="space-y-2">
                    <legend class="text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Alcance') }}</legend>
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-800 dark:text-slate-200">
                        <input type="radio" name="propagacao_escopo" value="todas" x-model="escopo" @checked($escopoOld === 'todas') class="border-slate-300 text-violet-600" />
                        {{ __('Todas as empresas (elegíveis)') }}
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-800 dark:text-slate-200">
                        <input type="radio" name="propagacao_escopo" value="selecionadas" x-model="escopo" @checked($escopoOld === 'selecionadas') class="border-slate-300 text-violet-600" />
                        {{ __('Apenas empresas seleccionadas') }}
                    </label>
                    @error('propagacao_escopo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </fieldset>
                <div>
                    <label for="nx-prop-empresas" class="block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-400">{{ __('Empresas') }}</label>
                    <select
                        id="nx-prop-empresas"
                        name="empresa_ids[]"
                        multiple
                        size="{{ $propagacaoSelectSize }}"
                        x-bind:disabled="escopo !== 'selecionadas'"
                        class="mt-1 w-full max-w-xl rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                    >
                        @foreach ($empresas as $e)
                            <option value="{{ $e->id }}" @selected(in_array((int) $e->id, $empresaIdsOld, true))>{{ $e->nome }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400" x-show="escopo === 'selecionadas'" x-cloak>{{ __('Mantenha Ctrl (Windows) ou Cmd (Mac) para escolher várias.') }}</p>
                    @error('empresa_ids')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @error('empresa_ids.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-start gap-2 text-sm text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="confirmar" value="1" required class="mt-1 rounded border-slate-300 text-violet-600" />
                    {{ __('Confirmo que desejo sobrescrever o conteúdo nas empresas elegíveis.') }}
                </label>
                @error('confirmar')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <button type="submit" class="rounded-lg bg-amber-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-amber-600">{{ __('Propagar') }}</button>
            </form>
        </div>

        <div class="rounded-2xl border border-red-200/80 bg-red-50/50 p-6 dark:border-red-900/40 dark:bg-red-950/20">
            <h3 class="text-sm font-semibold text-red-900 dark:text-red-200">{{ __('Eliminar permanentemente') }}</h3>
            <p class="mt-1 text-xs text-red-800/90 dark:text-red-300/90">
                @if ($refsEmpresa > 0)
                    {{ __('Existem :n modelo(s) de empresa ligados a este global. Pode eliminar só o registo global (as empresas mantêm uma cópia local e perdem o vínculo) ou apagar também todas as cópias nas empresas.', ['n' => $refsEmpresa]) }}
                @else
                    {{ __('Não há modelos de empresa ligados; o registo global será removido da base de dados.') }}
                @endif
            </p>
            <form
                method="POST"
                action="{{ route('platform.cadastros.documentos-automatizados.destroy', $modelo) }}"
                class="mt-4 space-y-3"
                onsubmit="const c = this.querySelector('input[name=apagar_copias_empresa]'); return confirm(c && c.checked ? @js(__('Isto remove o documento global e TODAS as cópias nas empresas. Continuar?')) : @js(__('Eliminar permanentemente este documento global?')));"
            >
                @csrf
                @method('DELETE')
                @if ($refsEmpresa > 0)
                    <label class="flex cursor-pointer items-start gap-2 text-sm text-red-900 dark:text-red-200">
                        <input type="checkbox" name="apagar_copias_empresa" value="1" class="mt-1 rounded border-red-300 text-red-700" />
                        <span>{{ __('Apagar também todos os modelos de empresa com este slug (irreversível).') }}</span>
                    </label>
                @endif
                <button type="submit" class="rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 dark:border-red-800 dark:bg-red-950/40 dark:text-red-200 dark:hover:bg-red-950/60">
                    {{ __('Eliminar global permanentemente') }}
                </button>
            </form>
        </div>
    </div>
</x-platform-layout>
