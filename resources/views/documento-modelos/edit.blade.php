<x-tenant-admin-layout title="{{ __('Editar modelo') }}">
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">
                {{ __('Editar modelo') }} — {{ $modelo->titulo }}
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ __('Você pode usar Blade com $cliente e $hoje (Carbon).') }}
            </p>
        </div>
    </x-slot>

    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl space-y-4">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-300">
                <p class="font-medium text-slate-900 dark:text-slate-100">{{ __('Onde está o modelo que o sistema usa?') }}</p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-xs">
                    <li>{{ __('A pré-visualização e o PDF usam o conteúdo guardado na base de dados (campo abaixo). Não leem automaticamente o ficheiro que abre no Cursor/VS Code.') }}</li>
                    <li>{{ __('Ao carregar um HTML ou ao premir Salvar aqui, o NorteX tenta copiar esse mesmo texto para :path (útil para Git e para editar no IDE).', ['path' => $caminhoPadraoRelativo]) }}</li>
                    <li>{{ __('Se editar só o .blade.php no IDE, as alterações não entram no PDF até copiar para este editor e Salvar — ou use o botão abaixo para gravar no disco o que já está na base de dados.') }}</li>
                </ul>
                @if (! empty($conteudoFicheiroDivergeDaBd))
                    <div class="mt-3 flex flex-wrap items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-amber-950 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-100">
                        <span class="text-xs font-medium">{{ __('O ficheiro em disco e o conteúdo na base de dados estão diferentes.') }}</span>
                        <form method="POST" action="{{ tenant_doc_modelo_route('sync-disco-bd', ['modelo' => $modelo]) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-full border border-amber-300 bg-white px-3 py-1 text-xs font-semibold text-amber-900 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200 dark:hover:bg-amber-900/50">
                                {{ __('Ler :slug → BD', ['slug' => $modelo->slug.'.blade.php']) }}
                            </button>
                        </form>
                        <form method="POST" action="{{ tenant_doc_modelo_route('sync-padrao-disco', ['modelo' => $modelo]) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-full border border-amber-300 bg-white px-3 py-1 text-xs font-semibold text-amber-900 hover:bg-amber-100 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200 dark:hover:bg-amber-900/50">
                                {{ __('Gravar na BD → ficheiro :slug', ['slug' => $modelo->slug.'.blade.php']) }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ tenant_doc_modelo_route('duplicate', ['modelo' => $modelo]) }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                        {{ __('Duplicar modelo') }}
                    </button>
                </form>
                @if (filled($modelo->mapeamento_upload) && is_array($modelo->mapeamento_upload))
                    <a href="{{ tenant_doc_modelo_route('verificacao', ['modelo' => $modelo]) }}" class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                        {{ __('Ver mapeamento do último upload') }}
                    </a>
                @endif
                @if (! empty($existePadrao))
                    <form method="POST" action="{{ tenant_doc_modelo_route('restore-default', ['modelo' => $modelo]) }}" class="inline" onsubmit="return confirm(@json(__('Substituir todo o conteúdo pelo ficheiro padrão do sistema?')))">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-xs font-semibold text-amber-900 hover:bg-amber-100 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200 dark:hover:bg-amber-950/60">
                            {{ __('Repor padrão do sistema') }}
                        </button>
                    </form>
                @endif
            </div>

            <form method="POST" action="{{ tenant_doc_modelo_route('update', ['modelo' => $modelo]) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-5 py-3 text-sm font-semibold text-slate-900 dark:border-slate-800 dark:text-slate-100">
                        {{ __('Dados do modelo') }}
                    </div>

                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Título') }}</label>
                            <input
                                name="titulo"
                                value="{{ old('titulo', $modelo->titulo) }}"
                                class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                            @error('titulo')
                                <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Referência') }}</label>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ __('Norma ou instrumento que originou o anexo (ex.: NORMAM-212/DPC).') }}</p>
                            <input
                                name="referencia"
                                value="{{ old('referencia', $modelo->referencia) }}"
                                maxlength="160"
                                class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            />
                            @error('referencia')
                                <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Conteúdo (HTML/Blade)') }}</label>
                            <textarea
                                name="conteudo"
                                rows="18"
                                class="mt-1 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 font-mono text-xs text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                            >{{ old('conteudo', $modelo->conteudo) }}</textarea>
                            @error('conteudo')
                                <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30"
                            >
                                {{ __('Salvar') }}
                            </button>
                            <a
                                href="{{ tenant_admin_route('documento-modelos.laboratorio') }}"
                                class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                            >
                                {{ __('Voltar') }}
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-tenant-admin-layout>

